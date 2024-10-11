<?php
// src/Command/SendNotificationsCommand.php
namespace App\Command;

use App\Repository\PiluleRepository;
use App\Service\NotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendNotificationsCommand extends Command
{
    protected static $defaultName = 'app:send-notifications';
    private $piluleRepository;
    private $notificationService;
    private $logFile;

    public function __construct(PiluleRepository $piluleRepository, NotificationService $notificationService)
    {
        parent::__construct();
        $this->piluleRepository = $piluleRepository;
        $this->notificationService = $notificationService;
        $this->logFile = __DIR__ . '/logs.log'; // Utilisez un chemin absolu
    }

    protected function configure()
    {
        $this
            ->setDescription('Send notifications for pills to be taken and reminders every 10 minutes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        //DateTime en France
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        //on vide le fichier de logs
        file_put_contents($this->logFile, "", LOCK_EX);


        file_put_contents($this->logFile, "Commande exécutée à " . $now->format('Y-m-d H:i:s') . "\n");

        try {
            file_put_contents($this->logFile, "Entrée dans le try à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);

            $pilules = $this->piluleRepository->findByHeureDePrise($now);
            file_put_contents($this->logFile, "". count($pilules) . " pilules trouvées avant affinage à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);

            foreach ($pilules as $pilule) {
                if ($pilule->estEnPause() || $pilule->piluleEstPriseAujourdhui()) {
                    unset($pilules[array_search($pilule, $pilules)]);
                }
            }

            file_put_contents($this->logFile, "". count($pilules) . " pilules correspondantes ont été trouvées à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);


            foreach ($pilules as $pilule) {
                $user = $pilule->getProprietaire();
                $message = "Il est temps de prendre votre traitement : " . $pilule->getLibelle();
                //$this->notificationService->sendNotification($user, $message);
                mail($user->getAdresseMail(), 'Il est l\'heure de prendre votre' . $pilule->getLibelle(), 'C\'est l\'heure ! Pensez à prendre votre traitement !', 'From:' . 'timepills@thomasloye.fr');
                file_put_contents($this->logFile, "Notification envoyée pour la pilule : " . $pilule->getLibelle() . "\n", FILE_APPEND);

                // Envoyer des rappels toutes les 10 minutes si la pilule n'a pas été prise
                $this->sendReminders($pilule, $user, $message);
                file_put_contents($this->logFile, "Lancement de la procédure de rappel : " . $pilule->getLibelle() . "\n", FILE_APPEND);

                $io->success('Notifications et rappels envoyés avec succès.');
            }

        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
        }

        $io->success('Commande terminée.');

        return Command::SUCCESS;
    }

    private function sendReminders($pilule, $user, $message): void
    {
        $reminderInterval = new \DateInterval('PT10M');
        $now = new \DateTime();

        while (!$this->isPillTaken($pilule) && $now < $this->getEndOfDay()) {
            sleep(600); // Attendre 10 minutes
            file_put_contents($this->logFile, "Rappel envoyé à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);
            //$this->notificationService->sendNotification($user, $message . " (Rappel)");
            mail($user->getAdresseMail(), 'Rappel : Il est l\'heure de prendre votre' . $pilule->getLibelle(), 'C\'est l\'heure ! Pensez à prendre votre traitement !', 'From:' . 'timepills@thomasloye.fr');
            $now->add($reminderInterval);
        }
    }

    private function isPillTaken($pilule): bool
    {
        return $pilule->piluleEstPriseAujourdhui();
    }

    private function getEndOfDay(): \DateTime
    {
        return (new \DateTime())->setTime(23, 59, 59);
    }
}