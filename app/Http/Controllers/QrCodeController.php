<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\FoundDocumentNotification;
use Illuminate\Support\Facades\Validator;

class QrCodeController extends Controller
{
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

        // Envoyer un SMS 
        $this->sendSMS($user->phone, "Votre document a été trouvé. Contactez le trouveur au $finderPhone.");

        return response()->json(['message' => 'Notifications envoyées avec succès']);
    }

    // Réutilisation de votre méthode sendSMS (assurez-vous qu'elle est dans AuthController ou déplacez-la dans un service)
    protected function sendSMS($phoneNumber, $message)
    {
        $phoneNumber = str_replace('+221', '', $phoneNumber);
        $phoneNumber = '221' . $phoneNumber;

        if (strlen($phoneNumber) < 12) {
            Log::error('Numéro de téléphone invalide : ' . $phoneNumber);
            return;
        }

        $clientId = config('services.orange.client_id');
        $clientSecret = config('services.orange.client_secret');
        $accessToken = $this->getAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            Log::error('Erreur : Impossible de récupérer le token d\'accès.');
            return;
        }

        $senderAddress = config('services.orange.sender_address');
        if (strpos($senderAddress, 'tel:') !== 0) {
            $senderAddress = 'tel:' . $senderAddress;
        }

        $url = 'https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($senderAddress) . '/requests';
        $data = [
            'outboundSMSMessageRequest' => [
                'address' => 'tel:+' . $phoneNumber,
                'outboundSMSTextMessage' => ['message' => $message],
                'senderAddress' => $senderAddress,
                'senderName' => "DocTrack",
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);
    }

    protected function getAccessToken($clientId, $clientSecret)
    {
        $url = 'https://api.orange.com/oauth/v3/token';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

}
