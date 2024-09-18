<?php
namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentPublishedNotification;
use Spatie\Permission\Traits\HasRoles;
use App\Http\Requests\StoreDeclarationDePerteRequest;

class DeclarationDePerteController extends Controller
{
    use HasRoles;
    // Autres méthodes...

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
            'user_id' => $user->id, // Associez l'utilisateur authentifié
        ]);

        $matchingDocuments = Document::where('document_type_id', $validatedData['document_type_id'])
            ->where('OwnerFirstName', $validatedData['FirstNameInDoc'])
            ->where('OwnerLastName', $validatedData['LastNameInDoc'])
            ->get();

        foreach ($matchingDocuments as $document) {
            $this->sendNotificationEmail($user, $document);
        }

        return response()->json([
            'success' => true,
            'message' => 'Déclaration de perte créée avec succès.',
            'data' => $declaration
        ], 201);
    }

    private function sendNotificationEmail($user, $document)
    {
        // Accédez au téléphone du propriétaire du document
        $Phone = $document->user->Phone; // Vérifiez que la relation 'user' est correcte

         // Envoyer le mail avec le document et le numéro de téléphone du propriétaire
    Mail::to($user->email)->send(new DocumentPublishedNotification($document, $Phone));
    }

     /**
     * Afficher toutes les déclarations de perte (uniquement pour les admins).
     */
    public function index()
    {
        // Vérifier si l'utilisateur a le rôle 'Admin'
        if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Vous devez être un administrateur pour voir cette liste.'
            ], 403);
        }

        // Récupérer toutes les déclarations avec les informations de l'utilisateur qui les a créées
        $declarations = DeclarationDePerte::with('user')->get(); // Charger la relation 'user'

        return response()->json([
            'success' => true,
            'data' => $declarations
        ]);
    }

    public function show($id)
    {
        // Vérifier si l'utilisateur a le rôle 'Admin'
        if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Vous devez être un administrateur pour voir cette déclaration.'
            ], 403);
        }

        // Récupérer la déclaration de perte avec les informations de l'utilisateur
        $declaration = DeclarationDePerte::with('user')->findOrFail($id); // Charger la relation 'user'

        return response()->json([
            'success' => true,
            'data' => $declaration
        ]);
    }

    public function destroy($id)
{
    // Vérifier si l'utilisateur a le rôle 'Admin'
    if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous devez être un administrateur pour supprimer cette déclaration.'
        ], 403);
    }

    // Trouver la déclaration de perte par son ID
    $declaration = DeclarationDePerte::findOrFail($id);

    // Supprimer la déclaration
    $declaration->delete();

    return response()->json([
        'success' => true,
        'message' => 'Déclaration de perte supprimée avec succès.'
    ]);
}


}
