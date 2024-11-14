<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OrangeSMSService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendSMS($to, $message)
    {
        try {
            // Obtenir le token d'accès
            $response = $this->client->post('https://api.orange.com/oauth/v2/token', [
                'auth' => [config('services.orange_sms.client_id'), config('services.orange_sms.client_secret')],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $token = json_decode($response->getBody(), true)['access_token'];

            // Envoyer le SMS
            $smsResponse = $this->client->post(config('services.orange_sms.api_url'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'outboundSMSMessageRequest' => [
                        'address' => 'tel:+'. $to,
                        'senderAddress' => 'tel:+'. config('services.orange_sms.sender'),
                        'outboundSMSTextMessage' => [
                            'message' => $message,
                        ],
                    ],
                ],
            ]);

            Log::info('SMS envoyé avec succès : ' . $smsResponse->getBody());
            return true;

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
            return false;
        }
    }
}
