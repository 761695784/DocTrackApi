<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Models\Document;
use App\Models\EmailLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\NewNotificationEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
            // Optionnel : enregistrer une erreur ou gérer le cas où aucun fichier valide n'est fourni
            return response()->json(['error' => 'Aucun fichier image valide fourni'], 400);
        }

        // Enregistre le document dans la base de données
        $document->save();

        // Notifier l'admin d'un nouveau document publié
        Notification::create([
            'message' => 'Un nouveau document a été publié : ' . $document->OwnerFirstName . ' ' . $document->OwnerLastName,
            // 'document_id' => $document->id,
            'is_read' => false, // Nouveau champ pour suivre l'état de lecture
        ]);

        // Recherche des déclarations de perte correspondantes (insensible à la casse)
        $declarations = DeclarationDePerte::whereRaw('LOWER(FirstNameIndoc) = ?', [strtolower($document->OwnerFirstName)])
            ->whereRaw('LOWER(LastNameIndoc) = ?', [strtolower($document->OwnerLastName)])
            ->get();

        foreach ($declarations as $declaration) {
            $user = $declaration->user; // Récupérer l'utilisateur qui a fait la déclaration
            $Phone = $document->user->Phone; // Récupérer le numéro de téléphone du propriétaire du document
            $documentUrl = route('documents.show', $document->id); // Générer l'URL pour afficher le document

            try {
                // Envoi de l'email
                Mail::to($user->email)->send(new DocumentPublishedNotification($document, $Phone, $documentUrl));

                // Enregistrer un log d'email dans la table email_logs
                \App\Models\EmailLog::create([
                    'from' => config('mail.from.address'),
                    'to' => $user->email,
                    'subject' => 'Correspondance à votre déclaration de perte',
                    'body' => 'Le document publié correspondant aux informations : ' .
                              $document->OwnerFirstName . ' ' . $document->OwnerLastName .
                              ' avec le numéro du publicateur : ' . $Phone,
                    'publisher_user_id' => $document->user->id,
                    'requester_user_id' => $user->id,
                    'document_id' => $document->id,
                    'declarant_user_id' => $user->id,
                ]);

                Log::info('Email log enregistré avec succès pour l\'utilisateur ' . $user->email);

            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi ou de l\'enregistrement de l\'email log : ' . $e->getMessage(), [
                    'publisher_user_id' => $document->user->id,
                    'requester_user_id' => $user->id,
                    'document_id' => $document->id,
                    'declarant_user_id' => $user->id,
                ]);
            }
            // Enregistrer une notification pour l'utilisateur
            Notification::create([
                'message' => 'Un document correspondant à une déclaration a été trouvé : ' . $document->OwnerFirstName . ' ' . $document->OwnerLastName,
                // 'declaration_de_perte_id' => $declaration->id,
                'is_read' => false, // Nouveau champ pour suivre l'état de lecture
            ]);
        }

        // Répondre avec le document créé
        return response()->json([
            'success' => true,
            'message' => 'Document créé avec succès.',
            'document' => $document
        ], 201);
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


    public function OwnPub()
    {
        // Récupère uniquement les documents de l'utilisateur connecté
        $documents = Document::where('user_id', Auth::id())->with(['user', 'documentType'])->get();

        // Retourne les documents en JSON, y compris les informations de l'utilisateur
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
        // Créer un client Guzzle pour faire une requête HTTP
        $client = new Client();
        $url = 'https://nominatim.openstreetmap.org/search';

        try {
            // Faire une requête GET à Nominatim
            $response = $client->get($url, [
                'query' => [
                    'q' => $location, // Le lieu dont tu veux obtenir les coordonnées
                    'format' => 'json', // Format de la réponse
                    'limit' => 1 // Limiter à un seul résultat
                ]
            ]);

            // Décoder la réponse JSON
            $data = json_decode($response->getBody(), true);

            // Vérifier si les données existent
            if (isset($data[0])) {
                return [
                    'latitude' => $data[0]['lat'],
                    'longitude' => $data[0]['lon']
                ];
            }

            // Si aucune coordonnée n'a été trouvée
            return [
                'latitude' => null,
                'longitude' => null
            ];
        } catch (\Exception $e) {
            // Gérer les erreurs
            return [
                'latitude' => null,
                'longitude' => null,
                'error' => 'Erreur lors de la récupération des coordonnées.'
            ];
        }
    }

    // Méthode pour récupérer les publications et leurs coordonnées
    public function getPublicationsByLocation()
    {
        $publications = Document::select('Location', DB::raw('COUNT(*) as publications'))
                        ->groupBy('Location')
                        ->get();

        $regions = $publications->map(function($publication) {
            // Utiliser la fonction getCoordinates pour obtenir les lat/long du lieu
            $coords = $this->getCoordinates($publication->Location);

            return [
                'name' => $publication->Location,
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
                'publications' => $publication->publications
            ];
        });

        return response()->json($regions);
    }


}
