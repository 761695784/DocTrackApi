<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreDocumentRequest;
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
        return response()->json($document); // Retourne les détails du document
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
