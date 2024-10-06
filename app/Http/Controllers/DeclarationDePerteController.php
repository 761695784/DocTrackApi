<?php
namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use App\Mail\DocumentPublishedNotification;
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
            ->whereRaw('LOWER(OwnerFirstName) = LOWER(?)', [$validatedData['FirstNameInDoc']])
            ->whereRaw('LOWER(OwnerLastName) = LOWER(?)', [$validatedData['LastNameInDoc']])
            ->get();

        foreach ($matchingDocuments as $document) {
            try {
                $this->sendNotificationEmail($user, $document);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi de la notification : ' . $e->getMessage());
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
    // $documentUrl = route('documents.show', $document->id);
    $documentUrl = route('https://sendoctrack.netlify.app/document/', $document->id);


    try {
        Mail::to($user->email)->send(new DocumentPublishedNotification($document, $Phone, $documentUrl));

        // Ajout des logs pour vérifier les données
        Log::info('Tentative d\'enregistrement de l\'email log : ', [
            'from' => config('mail.from.address'),
            'to' => $user->email,
            'subject' => 'Correspondance à votre déclaration de perte',
            'body' => 'Le document publié correspondant aux informations : ' .
                      $document->OwnerFirstName . ' ' . $document->OwnerLastName .
                      ' avec le téléphone : ' . $Phone,
        ]);

        \App\Models\EmailLog::create([
            'from' => config('mail.from.address'),
            'to' => $user->email,
            'subject' => 'Correspondance à votre déclaration de perte',
            'body' => 'Le document publié correspondant aux informations : ' .
                      $document->OwnerFirstName . ' ' . $document->OwnerLastName .
                      ' avec le téléphone : ' . $Phone,
        ]);

        Log::info('Email log enregistré avec succès.');
    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'enregistrement de l\'email log : ' . $e->getMessage());
    }
}

     /**
     * Afficher toutes les déclarations de perte (uniquement pour les admins).
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être authentifié pour effectuer cette action.'
            ], 401); // Code 401 Unauthorized
        }

        // Si l'utilisateur est un administrateur, récupérer toutes les déclarations
        if ($user->hasRole('Admin')) {
            $declarations = DeclarationDePerte::with('user')->get();
        } else {
            // Si l'utilisateur est un simple utilisateur, ne récupérer que ses propres déclarations
            $declarations = DeclarationDePerte::with('user')->where('user_id', $user->id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $declarations
        ]);
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

    // Récupérer les déclarations faites par l'utilisateur connecté
    $declarations = DeclarationDePerte::where('user_id', $user->id)->get();

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

        $declaration = DeclarationDePerte::with('user')->findOrFail($id);

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
            $declaration->delete();
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
