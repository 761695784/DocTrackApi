<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreDocumentRequest;
use App\Mail\DocumentPublishedNotification;
use App\Http\Requests\UpdateDocumentRequest;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::all(); // Récupère tous les documents
        return response()->json($documents); // Retourne les documents en JSON
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        // Valider la demande et récupérer le fichier image
        $validatedData = $request->validated();
        $imagePath = $request->file('image')->store('documents'); // Stocker le fichier

        // Créer un nouveau document dans la base de données
        $document = Document::create([
            'image' => $imagePath,
            'OwnerFirstName' => $validatedData['OwnerFirstName'],
            'OwnerLastName' => $validatedData['OwnerLastName'],
            'Location' => $validatedData['Location'],
            'statut' => 'non récupéré', // Valeur par défaut
            'document_type_id' => $validatedData['document_type_id'],
            'user_id' => Auth::id(),
        ]);

        // Recherche des déclarations de perte correspondantes
        $declarations = DeclarationDePerte::where('FirstNameIndoc', $document->OwnerFirstName)
            ->where('LastNameIndoc', $document->OwnerLastName)
            ->get();

        foreach ($declarations as $declaration) {
            $user = $declaration->user; // Récupérer l'utilisateur qui a fait la déclaration
            // $Phone = $document->Phone; // Numéro de téléPhone de l'auteur de la publication
            // $documentUrl = route('documents.show', $document->id); // URL de la publication
            $Phone = $document->user->Phone; // Récupérer le numéro de téléphone du propriétaire du document
            $documentUrl = route('documents.show', $document->id); // Générer l'URL pour afficher le document

            // Envoi de la notification par email
            Mail::to($user->email)->send(new DocumentPublishedNotification($document, $Phone, $documentUrl));
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
    public function show(Document $document)
    {
        // return response()->json($document); // Retourne les détails du document
        $document = Document::findOrFail($document);
        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $document->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Document mis à jour avec succès.',
            'document' => $document
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document supprimé avec succès.'
        ]);
    }
}
