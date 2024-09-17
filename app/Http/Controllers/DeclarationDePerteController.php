<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeclarationDePerteRequest;
use App\Http\Requests\UpdateDeclarationDePerteRequest;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Auth;

class DeclarationDePerteController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lister toutes les déclarations de perte.
     */
    public function index()
    {
        // Récupérer toutes les déclarations avec pagination
        $declarations = DeclarationDePerte::with('documentType', 'user')->paginate(10);

        // Retourner une réponse JSON
        return response()->json([
            'success' => true,
            'data' => $declarations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Créer une déclaration de perte (authentification obligatoire).
     */
    public function store(StoreDeclarationDePerteRequest $request)
    {
        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401);
        }

        // Valider les données (via StoreDeclarationDePerteRequest)
        $validatedData = $request->validated();

        // Créer la déclaration de perte
        $declaration = DeclarationDePerte::create([
            'Title' => $validatedData['Title'],
            'FirstNameInDoc' => $validatedData['FirstNameInDoc'],
            'LastNameInDoc' => $validatedData['LastNameInDoc'],
            'DocIdentification' => $validatedData['DocIdentification'] ?? null,
            'document_type_id' => $validatedData['document_type_id'], // Clé étrangère pour le type de document
        ]);

        // Retourner la déclaration créée
        return response()->json([
            'success' => true,
            'message' => 'Déclaration de perte créée avec succès.',
            'data' => $declaration
        ], 201);
    }

    /**
     * Display the specified resource.
     * Afficher une déclaration de perte spécifique.
     */
    public function show(DeclarationDePerte $declarationDePerte)
    {
        // Récupérer la déclaration de perte
        return response()->json([
            'success' => true,
            'data' => $declarationDePerte
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Mettre à jour une déclaration de perte (authentification obligatoire).
     */
    public function update(UpdateDeclarationDePerteRequest $request, DeclarationDePerte $declarationDePerte)
    {
        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();

        if (!$user || $user->id !== $declarationDePerte->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cette déclaration.'
            ], 403);
        }

        // Valider les données
        $validatedData = $request->validated();

        // Mettre à jour les champs modifiables
        $declarationDePerte->update($validatedData);

        // Retourner une réponse après la mise à jour
        return response()->json([
            'success' => true,
            'message' => 'Déclaration mise à jour avec succès.',
            'data' => $declarationDePerte
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Supprimer une déclaration de perte (authentification obligatoire).
     */
    public function destroy(DeclarationDePerte $declarationDePerte)
    {
        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();

        if (!$user || $user->id !== $declarationDePerte->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette déclaration.'
            ], 403);
        }

        // Supprimer la déclaration
        $declarationDePerte->delete();

        // Retourner une réponse après la suppression
        return response()->json([
            'success' => true,
            'message' => 'Déclaration supprimée avec succès.'
        ]);
    }
}
