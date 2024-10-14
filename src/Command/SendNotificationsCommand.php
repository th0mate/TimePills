<?php
// src/Command/SendNotificationsCommand.php
namespace App\Command;

use App\Entity\Rappel;
use App\Repository\PiluleRepository;
use App\Repository\RappelRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class SendNotificationsCommand extends Command
{
    protected static $defaultName = 'app:send-notifications';
    private $logFile;

    public function __construct(PiluleRepository $piluleRepository, RappelRepository $rappelRepository)
    {
        parent::__construct();
        $this->piluleRepository = $piluleRepository;
        $this ->rappelRepository = $rappelRepository;
        $this->logFile = __DIR__ . '/logs.log';
    }

    protected function configure()
    {
        $this
            ->setDescription('Send notifications for pills to be taken and reminders every 10 minutes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        if ($now->format('i') == '30') {
            file_put_contents($this->logFile, "", LOCK_EX);
        }

        //$this->test();

        file_put_contents($this->logFile, "---------------------------------------------------------- \n", FILE_APPEND);
        file_put_contents($this->logFile, "Commande exécutée à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);

        try {
            $pilules = $this->piluleRepository->findByHeureDePrise($now);
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
            return Command::FAILURE;
        }

        file_put_contents($this->logFile, "" . count($pilules) . " pilules trouvées avant affinage \n", FILE_APPEND);

        foreach ($pilules as $pilule) {
            if ($pilule->estEnPause() || $pilule->piluleEstPriseAujourdhui()) {
                unset($pilules[array_search($pilule, $pilules)]);
            }
        }


        file_put_contents($this->logFile, "Final : " . count($pilules) . " pilules correspondantes ont été trouvées \n", FILE_APPEND);


        foreach ($pilules as $pilule) {
            $user = $pilule->getProprietaire();

            file_put_contents($this->logFile, "Envoi d'un email pour " . $user->getAdresseMail() . "\n", FILE_APPEND);
            require __DIR__ . '/../../vendor/autoload.php';
            $mail = new PHPMailer;
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'timepills@thomasloye.fr';
                $mail->Password = 'Replay2020!';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->setFrom('timepills@thomasloye.fr', 'TimePills');
                $mail->addAddress($user->getAdresseMail(), $user->getPrenom());

                $mail->isHTML();
                $mail->Subject = 'Il est l\'heure de prendre votre ' . $pilule->getLibelle() . ' !';
                $mail->Body = file_get_contents(__DIR__ . '/mails/mailInitial.html');

                if (!$mail->send()) {
                    file_put_contents($this->logFile, "Erreur : " . $mail->ErrorInfo . "\n", FILE_APPEND);
                } else {
                    file_put_contents($this->logFile, "Notification envoyée pour la pilule :" . $pilule->getLibelle() . "\n", FILE_APPEND);
                }

                file_put_contents($this->logFile, "Création du rappel pour la pilule \n", FILE_APPEND);
                $this->creerRappel($pilule, $user);

                $io->success('Notifications et rappels envoyés avec succès.');


            } catch (\Exception $e) {
                $io->error('An error occurred: ' . $e->getMessage());
                file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        file_put_contents($this->logFile, "Envoi des rappels nécessaires \n", FILE_APPEND);
        $this->envoyerRappels();

        file_put_contents($this->logFile, "La procédure a été exécutée sans interruptions. \n", FILE_APPEND);
        file_put_contents($this->logFile, "---------------------------------------------------------- \n", FILE_APPEND);
        $io->success('Commande terminée.');

        return Command::SUCCESS;
    }

    private function envoyerRappels(): void
    {
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $now->setTime($now->format('H'), $now->format('i'));

        $dixMinutesPlusTot = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $dixMinutesPlusTot->setTime($dixMinutesPlusTot->format('H'), $dixMinutesPlusTot->format('i'));
        $dixMinutesPlusTot->sub(new \DateInterval('PT10M'));

        $rappels = $this->rappelRepository->findBy(['heureDerniereNotif' => $dixMinutesPlusTot]);
        file_put_contents($this->logFile, "Rappels trouvés : " . count($rappels) . "\n", FILE_APPEND);

        foreach ($rappels as $rappel) {
            $pilule = $this->piluleRepository->find($rappel->getIdPilule());
            $user = $pilule->getProprietaire();

            if ($this->isPillTaken($pilule)) {
                continue;
            }

            if ($now > $this->getEndOfDay()) {
                $this->rappelRepository->delete($rappel);
                continue;
            }

            require __DIR__ . '/../../vendor/autoload.php';
            $mail = new PHPMailer;
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'timepills@thomasloye.fr';
                $mail->Password = 'Replay2020!';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->setFrom('timepills@thomasloye.fr', 'TimePills');
                $mail->addAddress($user->getAdresseMail(), $user->getPrenom());

                $mail->isHTML();
                $mail->Subject = 'RAPPEL : Il est l\'heure de prendre votre ' . $pilule->getLibelle() . ' !';
                $mail->Body = file_get_contents(__DIR__ . '/mails/mailRappel.html');

                if (!$mail->send()) {
                    file_put_contents($this->logFile, "Erreur lors d'un rappel : " . $mail->ErrorInfo . "\n", FILE_APPEND);
                } else {
                    file_put_contents($this->logFile, "Rappel envoyé pour la pilule :" . $pilule->getLibelle() . "\n", FILE_APPEND);
                }


            } catch (\Exception $e) {
                file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
            }

            $rappel->setHeureDerniereNotif(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $this->rappelRepository->save($rappel);
        }
    }

    private function creerRappel($pilule, $user): void
    {
        if ($this->rappelRepository->findBy(['idPilule' => $pilule->getId()])) {
            return;
        }

        $rappel = new Rappel();
        $rappel->setIdPilule($pilule->getId());
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $now->setTime($now->format('H'), $now->format('i'));
        $rappel->setHeureDerniereNotif($now);
        $rappel->setAdresseMail($user->getAdresseMail());
        $this->rappelRepository->save($rappel);
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