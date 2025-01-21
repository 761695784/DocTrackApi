<?php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use App\Mail\DocumentPublishedNotification;
use App\Http\Requests\StoreDeclarationDePerteRequest;

class DeclarationDePerteController extends Controller
{
    use HasRoles;
    public function store(StoreDeclarationDePerteRequest $request)
    {
        $user = Auth::user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être authentifié pour effectuer cette action.'
        ], 401);
    }

    $validatedData = $request->validated();

    $declaration = DeclarationDePerte::create([
        'Title' => $validatedData['Title'],
        'FirstNameInDoc' => $validatedData['FirstNameInDoc'],
        'LastNameInDoc' => $validatedData['LastNameInDoc'],
        'DocIdentification' => $validatedData['DocIdentification'] ?? null,
        'document_type_id' => $validatedData['document_type_id'],
        'user_id' => $user->id,
    ]);

    // Création d'une notification
    Notification::create([
        'message' => 'Nouvelle déclaration de perte créée : ' . $declaration->Title . ' ' . $declaration->FirstNameInDoc . ' ' . $declaration->LastNameInDoc,
        'is_read' => false,
    ]);

    $matchingDocuments = Document::where('document_type_id', $validatedData['document_type_id'])
        ->whereRaw('LOWER(OwnerFirstName) = LOWER(?)', [$validatedData['FirstNameInDoc']])
        ->whereRaw('LOWER(OwnerLastName) = LOWER(?)', [$validatedData['LastNameInDoc']])
        ->get();

    foreach ($matchingDocuments as $document) {
        try {
            // Envoi de l'email
            $this->sendNotificationEmail($user, $document);

            // Envoi du SMS
            $phoneNumber = $user->phone; // Numéro de téléphone de l'utilisateur déclarant
            $documentUrl = 'https://sendoctrack.netlify.app/document/' . $document->id;

            $message = 'Un document correspondant à votre déclaration de perte a été trouvé : ' . $document->OwnerFirstName . ' ' . $document->OwnerLastName . '. Consultez-le ici : ' . $documentUrl;

            $this->sendSMS($phoneNumber, $message); // Appel à la méthode pour l'envoi de SMS

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification ou du SMS : ' . $e->getMessage());
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Déclaration de perte créée avec succès.',
        'data' => $declaration
    ], 201);
    }

    private function sendNotificationEmail($user, $document)
    {
        $Phone = $document->user->Phone;
        $documentUrl = 'https://sendoctrack.netlify.app/document/' . $document->id;

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
    }

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
     * Afficher toutes les déclarations de perte (uniquement pour les admins).
     */
    public function index()
    {
        $user = Auth::user();

        // Si l'utilisateur n'est pas authentifié, retourner une erreur
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        // Si l'utilisateur est un administrateur, récupérer toutes les déclarations (y compris celles supprimées)
        if ($user->hasRole('Admin')) {
            $declarations = DeclarationDePerte::withTrashed()->with(['user', 'documentType'])->get();
        } else {
            // Si l'utilisateur est un simple utilisateur, ne récupérer que ses propres déclarations (non supprimées)
            $declarations = DeclarationDePerte::with(['user', 'documentType'])->where('user_id', $user->id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $declarations
        ]);
    }


    public function trashedDeclarations()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        // Récupérer les déclarations supprimées de l'utilisateur connecté
        $declarations = DeclarationDePerte::onlyTrashed()
            ->where('user_id', $user->id)
            ->with('user')
            ->get();


        if ($declarations->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Aucune déclaration supprimée trouvée.',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $declarations
        ]);
    }




    public function restoreTrashedDeclaration($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        $declaration = DeclarationDePerte::onlyTrashed()->findOrFail($id);

        // Vérifier si l'utilisateur est le propriétaire de la déclaration
        if ($declaration->user_id === $user->id) {
            $declaration->restore(); // Restaurer la déclaration
            return response()->json([
                'success' => true,
                'message' => 'Déclaration restaurée avec succès.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous ne pouvez restaurer que vos propres déclarations.'
        ], 403);
    }




    public function getUserDeclarations()
{
    // Vérifier si l'utilisateur est authentifié
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être authentifié pour voir vos déclarations.'
        ], 401);
    }

    // Récupérer les déclarations faites par l'utilisateur connecté avec les informations de l'utilisateur et du type de document
    $declarations = DeclarationDePerte::where('user_id', $user->id)
        ->with(['user', 'documentType']) // Charger les relations user et documentType
        ->get();

    return response()->json([
        'success' => true,
        'data' => $declarations
    ]);
}


    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        // Charge les informations de l'utilisateur et le type de document
        $declaration = DeclarationDePerte::with(['user', 'documentType'])->findOrFail($id);

        // Si l'utilisateur est un simple utilisateur, vérifier qu'il est propriétaire de la déclaration
        if ($user->hasRole('Admin') || $declaration->user_id === $user->id) {
            return response()->json([
                'success' => true,
                'data' => $declaration
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous ne pouvez voir que vos propres déclarations.'
        ], 403);
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

        $declaration = DeclarationDePerte::findOrFail($id);

        // Si l'utilisateur est un simple utilisateur, vérifier qu'il est propriétaire de la déclaration
        if ($user->hasRole('Admin') || $declaration->user_id === $user->id) {
            $declaration->delete(); // Ceci marque la déclaration comme supprimée (soft delete)
            return response()->json([
                'success' => true,
                'message' => 'Déclaration de perte supprimée avec succès.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous ne pouvez supprimer que vos propres déclarations.'
        ], 403);
    }


}
