<?php
// src/Service/NotificationService.php
namespace App\Service;

use GuzzleHttp\Client;

class NotificationService
{
    private $client;
    private $appId;
    private $apiKey;

    public function __construct(string $appId, string $apiKey)
    {
        $this->client = new Client();
        $this->appId = $appId;
        $this->apiKey = $apiKey;
    }

    public function sendNotification(string $userId, string $message)
    {
        $response = $this->client->post('https://onesignal.com/api/v1/notifications', [
            'headers' => [
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'app_id' => $this->appId,
                'include_external_user_ids' => [$userId],
                'contents' => ['en' => $message],
            ],
        ]);

        return $response->getStatusCode() === 200;
    }
}
