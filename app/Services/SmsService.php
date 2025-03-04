<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SmsService
{
    public function sendSms($phoneNumber, $message)
    {
        // Nettoyer le numéro de téléphone
        $phoneNumber = $this->cleanPhoneNumber($phoneNumber);

        if (strlen($phoneNumber) < 12) {
            Log::error('Numéro de téléphone invalide : ' . $phoneNumber);
            return false;
        }

        $clientId = Config::get('services.orange.client_id');
        $clientSecret = Config::get('services.orange.client_secret');
        $accessToken = $this->getAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            Log::error('Erreur : Impossible de récupérer le token d\'accès.');
            return false;
        }

        $senderAddress = Config::get('services.orange.sender_address');
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

        if ($response === false) {
            Log::error('Erreur lors de l\'envoi du SMS : ' . curl_error($ch));
            return false;
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['outboundSMSMessageRequest'])) {
            Log::info('SMS envoyé avec succès à ' . $phoneNumber);
            return true;
        }

        Log::error('Erreur lors de l\'envoi du SMS : ' . $response);
        return false;
    }

    private function cleanPhoneNumber($phoneNumber)
    {
        $phoneNumber = str_replace('+221', '', $phoneNumber);
        return '221' . $phoneNumber;
    }

    private function getAccessToken($clientId, $clientSecret)
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
