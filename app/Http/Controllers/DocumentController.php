<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Models\Document;
use App\Models\EmailLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DeclarationDePerte;
use App\Services\OrangeSMSService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\NewNotificationEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreDocumentRequest;
use App\Mail\DocumentPublishedNotification;
use App\Http\Requests\UpdateDocumentRequest;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\RestitutionRequestNotification;

class DocumentController extends Controller
{
    use SoftDeletes;

    /**
     * Display a listing of the resource.
     */
/**
 * Display a listing of the resource.
 */
public function index()
{
    $user = Auth::user(); // Récupérer l'utilisateur authentifié

    // Si l'utilisateur est un admin, récupérer tous les documents (y compris ceux supprimés)
    if ($user && $user->hasRole('Admin')) {
        $documents = Document::withTrashed()->with(['user', 'documentType'])->get();
    } else {
        // Récupère uniquement les documents actifs (non supprimés)
        $documents = Document::whereNull('deleted_at')->with(['user', 'documentType'])->get();
    }

    // Retourne les documents en JSON, y compris les informations de l'utilisateur et du type de document
    return response()->json($documents);
}

public function getAllPublications(Request $request)
{
    // Vérifie si l'utilisateur est connecté
    if (Auth::check()) {
        // Récupère toutes les publications, y compris les supprimées (soft deleted)
        $documents = Document::withTrashed()->with(['user', 'documentType'])->get(); // Inclut les soft deletes et les infos de l'utilisateur

        return response()->json($documents); // Retourne le tableau directement
    } else {
        // Si l'utilisateur n'est pas connecté, retourne un message d'erreur
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non authentifié',
        ], 401); // Code 401 pour l'authentification non autorisée
    }
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        // Valider la demande et récupérer le fichier image
        $validatedData = $request->validated();
        $document = new Document();
        $document->fill([
            'OwnerFirstName' => $validatedData['OwnerFirstName'],
            'OwnerLastName' => $validatedData['OwnerLastName'],
            'Location' => $validatedData['Location'],
            'statut' => 'non récupéré', // Valeur par défaut
            'document_type_id' => $validatedData['document_type_id'],
            'user_id' => Auth::id(),
        ]);

        // Vérifie si un fichier image a été téléversé et est valide
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Enregistre l'image dans le dossier 'documents' du système de fichiers public
            $path = $request->file('image')->store('documents', 'public');
            // Génère l'URL accessible publiquement pour l'image
            $document->image = Storage::url($path);
        } else {
            return response()->json(['error' => 'Aucun fichier image valide fourni'], 400);
        }

        // Enregistre le document dans la base de données
        $document->save();

        // Notifier l'admin d'un nouveau document publié
        Notification::create([
            'message' => 'Un nouveau document a été publié : ' . $document->OwnerFirstName . ' ' . $document->OwnerLastName,
            'is_read' => false,
        ]);

        // Recherche des déclarations de perte correspondantes (insensible à la casse)
        $declarations = DeclarationDePerte::whereRaw('LOWER(FirstNameInDoc) = ?', [strtolower($document->OwnerFirstName)])
            ->whereRaw('LOWER(LastNameInDoc) = ?', [strtolower($document->OwnerLastName)])
            ->get();

        foreach ($declarations as $declaration) {
            $user = $declaration->user; // Récupérer l'utilisateur qui a fait la déclaration
            $phone = $user->phone; // Assurez-vous que le champ `phone` est correct et existe
            $documentUrl = 'https://sendoctrack.netlify.app/document/' . $document->id; // URL pour afficher le document

            try {
                // Envoi de l'email
                Mail::to($user->email)->send(new DocumentPublishedNotification($document, $phone, $documentUrl));

                        // Envoi du SMS
                        $phoneNumber = $user->phone; // Numéro de téléphone de l'utilisateur déclarant
                        $documentUrl = 'https://sendoctrack.netlify.app/document/' . $document->id;
                        $message = 'Un document correspondant à votre déclaration de perte a été trouvé : ' . $document->OwnerFirstName . ' ' . $document->OwnerLastName . '. Consultez-le ici : ' . $documentUrl;

                        $this->sendSMS($phoneNumber, $message); // Appel à la méthode pour l'envoi de SMS
                // Log de l'email
                \App\Models\EmailLog::create([
                    'from' => config('mail.from.address'),
                    'to' => $user->email,
                    'subject' => 'Correspondance à votre déclaration de perte',
                    'body' => 'Le document publié correspondant aux informations : ' .
                              $document->OwnerFirstName . ' ' . $document->OwnerLastName .
                              ' avec le numéro du publicateur : ' . $phone,
                    'publisher_user_id' => $document->user->id,
                    'requester_user_id' => $user->id,
                    'document_id' => $document->id,
                    'declarant_user_id' => $user->id,
                ]);

                Log::info('Email et SMS envoyés avec succès à ' . $user->email);

            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi de la notification : ' . $e->getMessage(), [
                    'publisher_user_id' => $document->user->id,
                    'requester_user_id' => $user->id,
                    'document_id' => $document->id,
                    'declarant_user_id' => $user->id,
                ]);
            }

            // Enregistrer une notification pour l'utilisateur
            Notification::create([
                'message' => 'Un document correspondant à une déclaration a été trouvé : ' . $document->OwnerFirstName . ' ' . $document->OwnerLastName,
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document créé avec succès.',
            'document' => $document
        ], 201);
    }

    // // Méthode pour envoyer un SMS via l'API Orange
    // protected function sendSMS($phoneNumber, $message)
    // {
    //     $clientId = 'd9zZG8QXjlc1eevAJksdUaGYq1qgzIhx'; // Remplacez par votre ID client
    //     $clientSecret = '1eRQoDQiKk5Tm03ZS0cHIkMzdriKDR3cp4yEJPypNFfw'; // Remplacez par votre secret client
    //     $accessToken = $this->getAccessToken($clientId, $clientSecret);

    //     $url = 'https://api.orange.com/smsmessaging/v1/outbound/tel%3A%2B' . urlencode('+221783549714') . '/requests';

    //     $data = [
    //         'outboundSMSMessageRequest' => [
    //             'address' => 'tel:+221' . $phoneNumber, // Format international
    //             'senderAddress' => 'tel:+221783549714',
    //             'senderName' => 'DocTrack',
    //             'outboundSMSTextMessage' => [
    //                 'message' => $message
    //             ]
    //         ]
    //     ];

    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $accessToken,
    //         'Content-Type' => 'application/json'
    //     ])->post($url, $data);

    //     if ($response->failed()) {
    //         Log::error('Erreur lors de l\'envoi du SMS : ' . $response->body());
    //     }
    // }

    // Méthode pour envoyer un SMS via l'API Orange
protected function sendSMS($phoneNumber, $message)
{
    $clientId = 'd9zZG8QXjlc1eevAJksdUaGYq1qgzIhx'; // Remplacez par votre ID client
    $clientSecret = '1eRQoDQiKk5Tm03ZS0cHIkMzdriKDR3cp4yEJPypNFfw'; // Remplacez par votre secret client
    $accessToken = $this->getAccessToken($clientId, $clientSecret);

    // L'adresse de l'expéditeur au format international
    $senderAddress = 'tel:+221783549714'; // Remplacez par votre numéro

    $url = 'http://api.orange.com/smsmessaging/v1/outbound/' . urlencode($senderAddress) . '/requests';

    $data = [
        'outboundSMSMessageRequest' => [
            'address' => 'tel:+221' . $phoneNumber, // Format international pour le destinataire
            'outboundSMSTextMessage' => [
                'message' => $message,
            ],
            'senderAddress' => $senderAddress,
            'senderName' => 'DocTrack', // Nom de l'expéditeur
        ]
    ];

    // Envoi de la requête POST à l'API Orange
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json',
    ])->post($url, $data);

    // Vérification de la réponse
    if ($response->successful()) {
        Log::info('SMS envoyé avec succès à ' . $phoneNumber);
    } else {
        Log::error('Erreur lors de l\'envoi du SMS : ' . $response->body());
    }
}

  // Méthode pour obtenir un jeton d'accès
protected function getAccessToken($clientId, $clientSecret)
{
    $url = 'https://api.orange.com/oauth/v2/token';
    $response = Http::withBasicAuth($clientId, $clientSecret)
        ->asForm()
        ->post($url, [
            'grant_type' => 'client_credentials',
        ]);

    if ($response->successful()) {
        return $response->json()['access_token'];
    }

    Log::error('Erreur lors de l\'obtention du jeton d\'accès : ' . $response->body());
    throw new \Exception('Impossible d\'obtenir le jeton d\'accès');
}
        /**
     * Gérer la demande de restitution.
     */
    public function requestRestitution($documentId)
    {
        // Récupérer le document concerné
        $document = Document::findOrFail($documentId);

        // L'utilisateur connecté qui clique sur "Restituer"
        $fromUser = Auth::user();

        // Vérifier si l'utilisateur connecté est le propriétaire du document
        if ($fromUser->id === $document->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas demander la restitution de votre propre document.'
            ], 403); // Code 403 Forbidden
        }

        // Vérifier si une demande de restitution pour ce document a déjà été faite par cet utilisateur
        $existingEmailLog = EmailLog::where('requester_user_id', $fromUser->id)
            ->where('document_id', $documentId)
            ->whereNotNull('publisher_user_id') // S'assurer qu'il s'agit bien d'une demande de restitution
            ->first();

        if ($existingEmailLog) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà demandé la restitution de ce document.'
            ], 400); // Code 400 Bad Request
        }

        // L'utilisateur qui a publié le document
        $toUser = $document->user;

        // Envoyer la notification par email
        $toUser->notify(new RestitutionRequestNotification($fromUser, $document));

        // Retourner une réponse JSON
        return response()->json(['message' => 'Demande de restitution envoyée avec succès.']);
    }


            /**
     * Display the specified resource.
     */
    public function show($id) {
        $document = Document::with(['user', 'documentType'])->find($id); // Charge les détails de l'utilisateur associé
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        return response()->json($document);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        // Vérifiez si l'utilisateur authentifié est le propriétaire du document
        if (Auth::id() !== $document->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à mettre à jour ce document.'
            ], 403); // Code 403 Forbidden
        }

        // Met à jour le document avec les données validées
        $validatedData = $request->validated(); // Valider la requête

        // Mise à jour uniquement des champs présents dans la requête
        $document->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Document mis à jour avec succès.',
            'document' => $document // Retourner le document mis à jour
        ]);
    }


    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        $document = Document::findOrFail($id);

        // Si l'utilisateur est un simple utilisateur, vérifier qu'il est propriétaire de la publication
        if ($user->hasRole('Admin') || $document->user_id === $user->id) {
            $document->delete(); // Ceci marque le document comme supprimé (soft delete)
            return response()->json([
                'success' => true,
                'message' => 'Publication supprimée avec succès.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous ne pouvez supprimer que vos propres publications.'
        ], 403);
    }

    public function restoreTrashedDocument($id)
    {
        $user = Auth::user(); // Récupérer l'utilisateur authentifié

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        // Trouver le document même s'il est soft deleted
        $document = Document::onlyTrashed()->findOrFail($id);

        // Vérifier si l'utilisateur est le propriétaire du document
        if ($document->user_id === $user->id) {
            $document->restore(); // Restaurer le document soft deleted
            return response()->json([
                'success' => true,
                'message' => 'Document restauré avec succès.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous ne pouvez restaurer que vos propres documents.'
        ], 403);
    }



    public function trashedDocuments()
    {
        $user = Auth::user(); // Récupérer l'utilisateur authentifié

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        // Récupérer uniquement les documents supprimés de l'utilisateur connecté
        $documents = Document::onlyTrashed()
            ->where('user_id', $user->id)
            ->with('user') // Charge les informations de l'utilisateur associé à chaque document
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    public function OwnPub()
    {
        // Récupère uniquement les documents de l'utilisateur connecté
        $documents = Document::where('user_id', Auth::id())->with(['user', 'documentType'])->get();

        // Retourne les documents en JSON, y compris les informations de l'utilisateur
        return response()->json($documents);
    }

    // Fonction pour obtenir uniquement les documents supprimés
    public function getDeletedDocuments()
    {
        $user = Auth::user(); // Récupérer l'utilisateur authentifié

        // Vérifier si l'utilisateur est un admin
        if (!$user || !$user->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Vous devez être un administrateur pour effectuer cette action.'
            ], 403);
        }

        // Récupérer uniquement les documents supprimés (soft deleted)
        $documents = Document::onlyTrashed()->with(['user', 'documentType'])->get();

        return response()->json($documents);
    }

    // Fonction pour obtenir les documents dont le statut est "récupéré"
    public function getRecoveredDocuments()
    {
        $user = Auth::user(); // Récupérer l'utilisateur authentifié

        // Vérifier si l'utilisateur est un admin
        if (!$user || !$user->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Vous devez être un administrateur pour effectuer cette action.'
            ], 403);
        }

        // Récupérer les documents dont le statut est "récupéré", y compris ceux qui sont soft-deleted
        $documents = Document::withTrashed()
            ->where('statut', 'récupéré')
            ->with(['user', 'documentType'])
            ->get();

        return response()->json($documents);
    }


   // Fonction pour obtenir les documents dont le statut est "non récupéré"
    public function getNotRecoveredDocuments()
    {
        $user = Auth::user(); // Récupérer l'utilisateur authentifié

        // Vérifier si l'utilisateur est un admin
        if (!$user || !$user->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Vous devez être un administrateur pour effectuer cette action.'
            ], 403);
        }

        // Récupérer les documents dont le statut est "non récupéré", y compris ceux qui sont soft-deleted
        $documents = Document::withTrashed()
            ->where('statut', 'non récupéré')
            ->with(['user', 'documentType'])
            ->get();

        return response()->json($documents);
    }



    public function getPublicationsByType()
    {
        $publications = Document::select('document_type_id', DB::raw('count(*) as count'))
                                ->groupBy('document_type_id')
                                ->with('documentType') // Charger le nom du type
                                ->get();

        return response()->json([
            'data' => $publications->map(function($publication) {
                return [
                    'typeName' => $publication->documentType->TypeName,
                    'count' => $publication->count,
                ];
            })
        ]);
    }

    public function getRestitutionData()
    {
        $restitutionCount = EmailLog::where('subject', 'LIKE', '%Demande de restitution%')->count();
        $publicationCount = Document::withTrashed()->count(); // ou avec les soft deleted si nécessaire

        return response()->json([
            'restitutionCount' => $restitutionCount,
            'publicationCount' => $publicationCount,
        ]);
    }

    public function getEmailActivity()
    {
        // Compte le nombre d'emails envoyés par sujet
        $emailCounts = EmailLog::select('subject', DB::raw('count(*) as count'))
            ->groupBy('subject')
            ->get();

        return response()->json($emailCounts);
    }


    /**
     * Get statistics of declarations and publications by date.
     */
    public function getStatistics()
    {
        // Récupérer les données par date
        $startDate = Carbon::now()->startOfMonth(); // Début du mois actuel
        $endDate = Carbon::now()->endOfMonth(); // Fin du mois actuel

        $data = [];

        // Boucle sur chaque jour du mois
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $formattedDate = $date->toDateString(); // Format de la date YYYY-MM-DD

            // Compte les déclarations de perte pour la date donnée
            $declarationsCount = DeclarationDePerte::whereDate('created_at', $formattedDate)->count();

            // Compte les publications pour la date donnée
            $publicationsCount = Document::whereDate('created_at', $formattedDate)->count();

            // Ajoute les données au tableau
            $data[] = [
                'date' => $formattedDate,
                'declarations' => $declarationsCount,
                'publications' => $publicationsCount,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function getDocumentStatusCountWithTrashed(Request $request)
    {
        // Vérifie si l'utilisateur est connecté
        if (Auth::check()) {
            // Récupère le nombre de documents avec le statut "récupéré", y compris ceux soft deleted
            $recoveredCount = Document::withTrashed()->where('statut', 'récupéré')->count();

            // Récupère le nombre de documents avec le statut "non récupéré", y compris ceux soft deleted
            $notRecoveredCount = Document::withTrashed()->where('statut', 'non récupéré')->count();

            // Retourne les résultats en JSON
            return response()->json([
                'récupéré' => $recoveredCount,
                'non_récupéré' => $notRecoveredCount,
            ]);
        } else {
            // Si l'utilisateur n'est pas connecté, retourne un message d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401); // Code 401 pour l'authentification non autorisée
        }
    }

    public function getCoordinates($location)
    {
        // Vérifie si les coordonnées sont déjà dans le cache
        $cacheKey = 'coordinates_' . $location;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $client = new Client();
        $url = 'https://api.opencagedata.com/geocode/v1/json'; // Limites gratuites : Jusqu'à 2 500 requêtes par jour.

        try {
            $response = $client->get($url, [
                'query' => [
                    'q' => $location,
                    'key' => '39195082031d4014b230dcb3433133b3', //  clé API OpenCage
                    'countrycode' => 'SN', // Code pays pour restreindre la recherche au Sénégal
                    'limit' => 1 // Limite le nombre de résultats
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            $coordinates = [
                'latitude' => $data['results'][0]['geometry']['lat'] ?? null,
                'longitude' => $data['results'][0]['geometry']['lng'] ?? null
            ];

            // Mettre les coordonnées dans le cache pour une durée de 48 heures
            Cache::put($cacheKey, $coordinates, 172800);

            return $coordinates;
        } catch (\Exception $e) {
            return [
                'latitude' => null,
                'longitude' => null,
                'error' => 'Erreur lors de la récupération des coordonnées.'
            ];
        }
    }

//     public function getCoordinates($location)
// {
//     $cacheKey = 'coordinates_' . $location;
//     if (Cache::has($cacheKey)) {
//         return Cache::get($cacheKey);
//     }

//     $client = new Client();
//     $url = 'https://us1.locationiq.com/v1/search.php'; // Limites gratuites : Jusqu'à 5 000 requêtes gratuites par jour.

//     try {
//         $response = $client->get($url, [
//             'query' => [
//                 'key' => 'pk.5c10c9c439934952e54b7cc9a53a5474', // Remplace par ta clé API
//                 'q' => $location,
//                 'format' => 'json',
//                 'limit' => 1
//             ]
//         ]);

//         $data = json_decode($response->getBody(), true);

//         $coordinates = [
//             'latitude' => $data[0]['lat'] ?? null,
//             'longitude' => $data[0]['lon'] ?? null
//         ];

//         Cache::put($cacheKey, $coordinates, 86400);

//         return $coordinates;
//     } catch (\Exception $e) {
//         return [
//             'latitude' => null,
//             'longitude' => null,
//             'error' => 'Erreur lors de la récupération des coordonnées.'
//         ];
//     }
// }


// public function getCoordinates($location)
// {
//     $cacheKey = 'coordinates_' . $location;
//     if (Cache::has($cacheKey)) {
//         return Cache::get($cacheKey);
//     }

//     $client = new Client();
//     $url = 'https://geocode.search.hereapi.com/v1/geocode'; // Limites gratuites : 250 000 transactions par mois.

//     try {
//         $response = $client->get($url, [
//             'query' => [
//                 'q' => $location,
//                 'apiKey' => 'MXUuNBUTEgr5l1rZSVFDxE5qvITOyi55mKgmRR0ZwA8' // Remplace par ta clé API
//             ]
//         ]);

//         $data = json_decode($response->getBody(), true);

//         $coordinates = [
//             'latitude' => $data['items'][0]['position']['lat'] ?? null,
//             'longitude' => $data['items'][0]['position']['lng'] ?? null
//         ];

//         Cache::put($cacheKey, $coordinates, 86400);

//         return $coordinates;
//     } catch (\Exception $e) {
//         return [
//             'latitude' => null,
//             'longitude' => null,
//             'error' => 'Erreur lors de la récupération des coordonnées.'
//         ];
//     }
// }


    // Méthode pour récupérer les publications et leurs coordonnées
    public function getPublicationsByLocation()
    {
        $localities = Document::distinct()->pluck('Location');

        $regions = $localities->map(function($location) {
            // Vérifie ou génère les coordonnées
            $coords = $this->getCoordinates($location);

            return [
                'name' => $location,
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ];
        });

        $publications = Document::select('Location', DB::raw('COUNT(*) as publications'))
                        ->groupBy('Location')
                        ->get()
                        ->keyBy('Location');

        $regions = $regions->map(function($region) use ($publications) {
            $region['publications'] = $publications[$region['name']]->publications ?? 0;
            return $region;
        });

        return response()->json($regions);
    }



}
