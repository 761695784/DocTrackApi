<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $document = Document::all(); // Récupère tous les types de documents
        return response()->json($document); // Retourne les types de documents en JSON
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
{
    // Valider la demande et récupérer le fichier image
    $validatedData = $request->validated();
    $imagePath = $request->file('image')->store('documents'); // Stocker le fichier

    // Appeler l'API OCR
    $ocrApiKey = env('OCR_API_KEY', 'K86248653888957'); // Assurez-vous d'avoir défini cette clé dans votre fichier .env
    $ocrApiUrl = 'https://api.ocr.space/parse/image'; // URL de l'API OCR

    try {
        $response = Http::asMultipart()->post($ocrApiUrl, [
            'apikey' => $ocrApiKey,
            'file' => fopen(storage_path("app/{$imagePath}"), 'r'),
        ]);

        if ($response->successful()) {
            // Analyser la réponse de l'API OCR
            $responseBody = $response->json();
            $text = $responseBody['ParsedResults'][0]['ParsedText'] ?? '';

            // Extraire le numéro d'identification à partir du texte
            $DocIdentification= $this->extractIdentificationNumber($text);

            // Créer un nouveau document dans la base de données
            $document = Document::create([
                'image' => $imagePath,
                'DocIdentification' => $DocIdentification,
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
        } else {
            // Renvoie un message d'erreur si l'API OCR échoue
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'appel à l\'API OCR.',
                'error' => $response->json()
            ], 500);
        }
    } catch (\Exception $e) {
        // Gérer les exceptions générales, en retournant un message d'erreur
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'appel à l\'API OCR.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Extraire le numéro d'identification du texte OCR basé sur "n°".
 */
protected function extractIdentificationNumber($text)
{
    // Utilisation d'une expression régulière pour trouver "n°" suivi d'un numéro
    // Ajustez-la selon vos besoins pour capturer uniquement les formats que vous attendez.
    preg_match('/n°\s*(\d+)/i', $text, $matches);
    return $matches[1] ?? ''; // Récupérer le numéro associé ou retourner une chaîne vide
}
    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        //
    }
}
