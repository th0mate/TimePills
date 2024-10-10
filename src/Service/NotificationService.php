<?php
// src/Service/NotificationService.php
namespace App\Service;

use App\Entity\Utilisateur;
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

    public function sendNotification(Utilisateur $user, string $message)
    {
        //on envoie une notification push à tous les OneSignalId de l'utilisateur
        foreach ($user->getOneSignalIds() as $oneSignalId) {
            $this->sendNotificationToOneSignalId($oneSignalId->getOneSignalId(), $message);
        }
    }

    private function sendNotificationToOneSignalId(string $oneSignalId, string $message)
    {
        dump('notification envoyée');
        $this->client->post('https://onesignal.com/api/v1/notifications', [
            'headers' => [
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'app_id' => $this->appId,
                'include_player_ids' => [$oneSignalId],
                'contents' => ['en' => $message],
            ],
        ]);
    }
}
