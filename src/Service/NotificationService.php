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
        $logFile = __DIR__ . '/logs.log';
        $oneSignalIds = [];

        // Collecter tous les OneSignalId de l'utilisateur
        foreach ($user->getOneSignalIds() as $oneSignalId) {
            $oneSignalIds[] = $oneSignalId->getOneSignalId();
            file_put_contents($logFile, "Envoi à l'utilisateur " . $user->getId() . " à l'OneSignalId " . $oneSignalId->getOneSignalId() . "\n", FILE_APPEND);
        }

        // Envoyer la notification à tous les OneSignalId en une seule requête
        $this->sendNotificationToOneSignalIds($oneSignalIds, $message);
    }

    private function sendNotificationToOneSignalIds(array $oneSignalIds, string $message)
    {
        $this->client->post('https://onesignal.com/api/v1/notifications', [
            'headers' => [
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'app_id' => $this->appId,
                'include_player_ids' => $oneSignalIds, // Envoyer à tous les IDs
                'contents' => ['en' => $message],
                'data' => ['url' => 'https://timepills.thomasloye.fr/utilisateur/medicaments'],
                'priority' => 10, // Priorité haute
                'content_available' => true, // Pour réveiller l'application même en arrière-plan
            ],
        ]);
    }
}
