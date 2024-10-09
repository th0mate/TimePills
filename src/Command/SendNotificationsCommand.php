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

    public function __construct(PiluleRepository $piluleRepository, NotificationService $notificationService)
    {
        parent::__construct();
        $this->piluleRepository = $piluleRepository;
        $this->notificationService = $notificationService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send notifications for pills to be taken and reminders every 10 minutes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        //on récupère toutes les pilules dont l'heure de prise est égale à l'heure actuelle
        $pilules = $this->piluleRepository->findByHeureDePrise($now);

        foreach ($pilules as $pilule) {
            $user = $pilule->getProprietaire();
            $message = "Il est temps de prendre votre pilule: " . $pilule->getLibelle();
            $this->notificationService->sendNotification($user->getId(), $message);

            // Envoyer des rappels toutes les 10 minutes si la pilule n'a pas été prise
            $this->sendReminders($pilule, $user->getId(), $message);
        }

        $io->success('Notifications and reminders sent successfully.');

        return Command::SUCCESS;
    }

    private function sendReminders($pilule, $userId, $message): void
    {
        $reminderInterval = new \DateInterval('PT10M');
        $now = new \DateTime();

        while (!$this->isPillTaken($pilule) && $now < $this->getEndOfDay()) {
            sleep(600); // Attendre 10 minutes
            $this->notificationService->sendNotification($userId, $message . " (Rappel)");
            $now->add($reminderInterval);
        }
    }

    private function isPillTaken($pilule): bool
    {
        // Implémentez la logique pour vérifier si la pilule a été prise
        return false;
    }

    private function getEndOfDay(): \DateTime
    {
        return (new \DateTime())->setTime(23, 59, 59);
    }
}