<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\FoundDocumentNotification;
use Illuminate\Support\Facades\Validator;
use App\Services\SmsService;

class QrCodeController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handleFoundQr(Request $request)
    {
        // Vérifier les règles de validation
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'finder_phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez fournir des données valides.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer les données envoyées par le frontend
        $token = $request->input('token');
        $finderPhone = $request->input('finder_phone');

        $user = User::where('qr_code_token', $token)
            ->where('qr_code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'QR code invalide ou expiré.'
            ], 404);
        }

        // Envoyer un email
        Mail::to($user->email)->send(new FoundDocumentNotification($finderPhone));

        // Envoyer un SMS via le service
        if (!$this->smsService->sendSms($user->phone, "Votre document a été trouvé. Contactez le trouveur au $finderPhone.")) {
            Log::error('Échec de l’envoi du SMS au numéro ' . $user->phone);
            // Tu peux choisir de retourner une erreur ou de continuer
        }

        return response()->json(['message' => 'Notifications envoyées avec succès']);
    }
}
