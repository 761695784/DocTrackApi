<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use App\Events\NewNotificationEvent;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreDocumentRequest;
use App\Mail\DocumentPublishedNotification;
use App\Http\Requests\UpdateDocumentRequest;
use App\Notifications\RestitutionRequestNotification;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // Récupère tous les documents avec les informations de l'utilisateur associé
    $documents = Document::with('user')->get();

    // Retourne les documents en JSON, y compris les informations de l'utilisateur
    return response()->json($documents);
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

        // Enregistre la ressource dans la base de données
        $document->save();

        // Recherche des déclarations de perte correspondantes (insensible à la casse)
        $declarations = DeclarationDePerte::whereRaw('LOWER(FirstNameIndoc) = ?', [strtolower($document->OwnerFirstName)])
            ->whereRaw('LOWER(LastNameIndoc) = ?', [strtolower($document->OwnerLastName)])
            ->get();

        foreach ($declarations as $declaration) {
            $user = $declaration->user; // Récupérer l'utilisateur qui a fait la déclaration
            $Phone = $document->user->Phone; // Récupérer le numéro de téléphone du propriétaire du document
            $documentUrl = route('documents.show', $document->id); // Générer l'URL pour afficher le document

            // Envoi de la notification par email
            Mail::to($user->email)->send(new DocumentPublishedNotification($document, $Phone, $documentUrl));

            // Émettre un événement de notification
            event(new NewNotificationEvent([
                'title' => 'Nouveau document',
                'message' => "Le document de {$document->OwnerFirstName} {$document->OwnerLastName} a été créé.",
                'type' => 'document'
            ]));
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
        $document = Document::with('user')->find($id); // Charge les détails de l'utilisateur associé
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

        // Si l'utilisateur est un simple utilisateur, vérifier qu'il est propriétaire de la déclaration
        if ($user->hasRole('Admin') || $document->user_id === $user->id) {
            $document->delete();
            return response()->json([
                'success' => true,
                'message' => 'Publications supprimée avec succès.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous ne pouvez supprimer que vos propres publications.'
        ], 403);
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
    public function sendNotification(Request $request)
    {
        $message = $request->input('message');
        broadcast(new NewNotificationEvent($message));  // Diffusion de l'événement
        return response()->json(['status' => 'Notification sent!']);
    }


    public function OwnPub()
{
    // Récupère uniquement les documents de l'utilisateur connecté
    $documents = Document::where('user_id', Auth::id())->with('user')->get();

    // Retourne les documents en JSON, y compris les informations de l'utilisateur
    return response()->json($documents);
}
}
