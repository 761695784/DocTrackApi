<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CertificatDePerte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificatDePerteController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est bien admin
        if (!$user->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        // Récupération de tous les certificats avec lien PDF
        $certificats = CertificatDePerte::with('declarationDePerte.documentType', 'declarationDePerte.user')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($certificat) {
                return [
                    'id' => $certificat->id,
                    'nom' => $certificat->declarationDePerte->LastNameInDoc,
                    'prenom' => $certificat->declarationDePerte->FirstNameInDoc,
                    'document_type' => $certificat->declarationDePerte->documentType->TypeName ?? null,
                    'pdf_url' => asset('storage/' . $certificat->pdf_path), // lien vers le fichier PDF
                    'created_at' => $certificat->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'certificats' => $certificats
        ]);
    }
    public function mesCertificats()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.'
            ], 401);
        }

        // Récupération de ses certificats via ses déclarations
        $certificats = CertificatDePerte::with('declarationDePerte.documentType')
            ->whereHas('declarationDePerte', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($certificat) {
                return [
                    'id' => $certificat->id,
                    'nom' => $certificat->declarationDePerte->LastNameInDoc,
                    'prenom' => $certificat->declarationDePerte->FirstNameInDoc,
                    'document_type' => $certificat->declarationDePerte->documentType->TypeName ?? null,
                    'pdf_url' => asset('storage/' . $certificat->pdf_path),
                    'created_at' => $certificat->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'certificats' => $certificats
        ]);
    }



    public function telecharger($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.'
            ], 401);
        }

        $certificat = CertificatDePerte::with('declarationDePerte')
            ->findOrFail($id);

        if ($certificat->declarationDePerte->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce certificat.'
            ], 403);
        }

        $pdfPath = storage_path('app/public/' . $certificat->pdf_path);

        if (!file_exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier PDF introuvable.'
            ], 404);
        }

        return response()->download($pdfPath, "certificat-perte-{$certificat->id}.pdf");
    }
}
