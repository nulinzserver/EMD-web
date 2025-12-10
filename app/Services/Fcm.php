<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Fcm
{
    protected string $projectId;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID');
    }

    /**
     * Send FCM Notification (Web or Mobile)
     */
    public function send_notify(string $token, array $data)
    {
        // Get access token (cached)
        $accessToken = Cache::remember('fcm_access_token', 55 * 60, function () {
            Log::debug('⚡ Generating NEW FCM Access Token');

            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                storage_path('app/emd.json')
            );

            $token = $credentials->fetchAuthToken();

            return $token['access_token'] ?? null;
        });

        Log::debug('✔ Using FCM Access Token: '.$accessToken);

        // Build payload directly
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $data['title'] ?? '',
                    'body' => $data['body'] ?? '',
                ],
                'data' => [
                    'title' => $data['title'] ?? '',
                    'body' => $data['body'] ?? '',
                    // 'link' => $data['link'] ?? 'https://onstru.com/',
                    // 'id' => (string) ($data['id'] ?? ''),
                ],
            ],
        ];

        // Web push additional options
        // if ($type === 'web') {
        //     $payload['message']['webpush'] = [
        //         'fcm_options' => [
        //             'link' => $data['link'] ?? 'https://onstru.com/',
        //         ],
        //     ];
        // }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $response = Http::withToken($accessToken)->post($url, $payload);

        Log::info('📩 FCM Sent', [
            // 'type' => $type,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->json();
    }
}
