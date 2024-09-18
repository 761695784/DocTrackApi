<?php
namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\DeclarationDePerte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentPublishedNotification;
use App\Http\Requests\StoreDeclarationDePerteRequest;

class DeclarationDePerteController extends Controller
{
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

}
