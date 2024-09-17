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
        //
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

        $response = Http::asMultipart()
            ->post($ocrApiUrl, [
                'apikey' => $ocrApiKey,
                'file' => fopen(storage_path("app/{$imagePath}"), 'r'),
            ]);

        // Analyser la réponse de l'API OCR
        $responseBody = $response->json();
        $text = $responseBody['ParsedResults'][0]['ParsedText'] ?? '';

        // Extraire prénom et nom à partir du texte
        $ownerFirstName = $this->extractOwnerFirstName($text);
        $ownerLastName = $this->extractOwnerLastName($text);

        // Créer un nouveau document
        $document = Document::create([
            'image' => $imagePath,
            'OwnerFirstName' => $ownerFirstName,
            'OwnerLastName' => $ownerLastName,
            'DocIdentification' => $validatedData['DocIdentification'] ?? null,
            'Location' => $validatedData['Location'],
            'statut' => 'non récupéré', // Valeur par défaut
            'document_type_id' => $validatedData['document_type_id'],
            'user_id' => Auth::id(),
        ]);

        return response()->json($document, 201);
    }

    /**
     * Extraire le prénom du texte OCR.
     */
    protected function extractOwnerFirstName($text)
    {
        // Exemple de logique d'extraction pour le prénom
        preg_match('/Prénoms:\s*(\w+)/i', $text, $matches);
        return $matches[1] ?? ''; // Récupérer le prénom ou retourner une chaîne vide
    }

    /**
     * Extraire le nom de famille du texte OCR.
     */
    protected function extractOwnerLastName($text)
    {
        // Exemple de logique d'extraction pour le nom de famille
        preg_match('/Nom:\s*(\w+)/i', $text, $matches);
        return $matches[1] ?? ''; // Récupérer le nom ou retourner une chaîne vide
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
