<?php
// src/Command/SendNotificationsCommand.php
namespace App\Command;

use App\Repository\PiluleRepository;
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

    public function __construct(PiluleRepository $piluleRepository)
    {
        parent::__construct();
        $this->piluleRepository = $piluleRepository;
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

        if ($now->format('i') == '00' || $now->format('i') == '05') {
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

        file_put_contents($this->logFile, "FLAG à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);
        file_put_contents($this->logFile, "" . count($pilules) . " pilules trouvées avant affinage à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);

        foreach ($pilules as $pilule) {
            if ($pilule->estEnPause() || $pilule->piluleEstPriseAujourdhui()) {
                unset($pilules[array_search($pilule, $pilules)]);
            }
        }


        file_put_contents($this->logFile, "" . count($pilules) . " pilules correspondantes ont été trouvées à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);


        foreach ($pilules as $pilule) {
            $user = $pilule->getProprietaire();

            file_put_contents($this->logFile, "Envoi d'un email pour " . $user->getAdresseMail() ."\n", FILE_APPEND);
            require __DIR__ . '/../../vendor/autoload.php';
            $mail = new PHPMailer;
            file_put_contents($this->logFile, "FLAG \n", FILE_APPEND);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'timepills@thomasloye.fr';
                $mail->Password = 'Replay2020!';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->SMTPDebug = 2;
                $mail->Port = 465;

                $mail->setFrom('timepills@thomasloye.fr', 'TimePills');
                $mail->addAddress($user->getAdresseMail(), $user->getPrenom());

                $mail->isHTML();
                $mail->Subject = 'Il est l\'heure de prendre votre ' . $pilule->getLibelle() . '!';
                $mail->Body = $this->getHtmlMessageInitial();

                if (!$mail->send()) {
                    file_put_contents($this->logFile, "Erreur : " . $mail->ErrorInfo . "\n", FILE_APPEND);
                } else {
                    file_put_contents($this->logFile, "Notification envoyée pour la pilule :". $pilule->getLibelle() ."\n", FILE_APPEND);
                }

                //$this->sendReminders($pilule, $user);
                //file_put_contents($this->logFile, "Lancement de la procédure de rappel : \n", FILE_APPEND);

                $io->success('Notifications et rappels envoyés avec succès.');


            } catch (\Exception $e) {
                $io->error('An error occurred: ' . $e->getMessage());
                file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        file_put_contents($this->logFile, "Si nécessaire, des mails ont bien été envoyés. \n", FILE_APPEND);
        file_put_contents($this->logFile, "---------------------------------------------------------- \n", FILE_APPEND);
        $io->success('Commande terminée.');

        return Command::SUCCESS;
    }

    private function test()
    {
        file_put_contents($this->logFile, "Envoie d'un mail de test... \n", FILE_APPEND);
        require __DIR__ . '/../../vendor/autoload.php';
        $mail = new PHPMailer;
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'timepills@thomasloye.fr';
            $mail->Password = 'Replay2020!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPDebug = 2;
            $mail->Port = 465; // Utilisez le port approprié pour votre serveur SMTP

            $mail->setFrom('timepills@thomasloye.fr', 'TimePills');
            $mail->addAddress('thomasloye1@gmail.com', 'Thomas Loye');

            $mail->isHTML(true);
            $mail->Subject = 'Il est l\'heure de prendre votre !';
            $mail->Body = 'HIUEHFKDHFKDJ';

            if (!$mail->send()) {
                file_put_contents($this->logFile, "Erreur : " . $mail->ErrorInfo . "\n", FILE_APPEND);
            } else {
                file_put_contents($this->logFile, "Notification envoyée pour la pilule : \n", FILE_APPEND);
            }
        } catch (\Exception $e) {
            file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    private function sendReminders($pilule, $user): void
    {
        $reminderInterval = new \DateInterval('PT10M');
        $now = new \DateTime();

        while (!$this->isPillTaken($pilule) && $now < $this->getEndOfDay()) {
            sleep(600); // Attendre 10 minutes
            file_put_contents($this->logFile, "Rappel envoyé à " . $now->format('Y-m-d H:i:s') . "\n", FILE_APPEND);

            require __DIR__ . '/../../vendor/autoload.php';
            $mail = new PHPMailer;
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'timepills@thomasloye.fr';
                $mail->Password = 'Replay2020!';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->SMTPDebug = 2;
                $mail->Port = 465;

                $mail->setFrom('timepills@thomasloye.fr', 'TimePills');
                $mail->addAddress($user->getAdresseMail(), $user->getPrenom());

                $mail->isHTML(true);
                $mail->Subject = 'RAPPEL : Il est l\'heure de prendre votre ' . $pilule->getLibelle() . '!';
                $mail->Body = $this->getHtmlMessageReminder();

                if (!$mail->send()) {
                    file_put_contents($this->logFile, "Erreur : " . $mail->ErrorInfo . "\n", FILE_APPEND);
                } else {
                    file_put_contents($this->logFile, "Notification envoyée pour la pilule : \n", FILE_APPEND);
                }

                file_put_contents($this->logFile, "Procédure de rappel envoyée et encore en cours\n", FILE_APPEND);


            } catch (\Exception $e) {
                file_put_contents($this->logFile, "Erreur : " . $e->getMessage() . "\n", FILE_APPEND);
            }


            $now->add($reminderInterval); // sert à incrémenter la date de 10 minutes
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

    private function getHtmlMessageInitial()
    {
        return <<<HTML
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="fr"><head><meta charset="UTF-8"><meta content="width=device-width, initial-scale=1" name="viewport"><meta name="x-apple-disable-message-reformatting"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta content="telephone=no" name="format-detection"><title>New Message</title> <!--[if (mso 16)]><style type="text/css">     a {text-decoration: none;}     </style><![endif]--> <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--> <!--[if gte mso 9]><noscript> <xml> <o:OfficeDocumentSettings> <o:AllowPNG></o:AllowPNG> <o:PixelsPerInch>96</o:PixelsPerInch> </o:OfficeDocumentSettings> </xml> </noscript>
<![endif]--> <!--[if mso]><style type="text/css">    ul {   margin: 0 !important; } ol {   margin: 0 !important; } li {   margin-left: 47px !important; }  </style>
<![endif] --> <!--[if !mso]><!-- --><link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet"><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i"> <!--<![endif]--><style type="text/css">.rollover:hover .rollover-first { max-height:0px!important; display:none!important;}.rollover:hover .rollover-second { max-height:none!important; display:block!important;}.rollover span { font-size:0px;}u + .body img ~ div div { display:none;}#outlook a { padding:0;}span.MsoHyperlink,span.MsoHyperlinkFollowed { color:inherit; mso-style-priority:99;} a.es-button { mso-style-priority:100!important; text-decoration:none!important;}a[x-apple-data-detectors],#MessageViewBody a { color:inherit!important; text-decoration:none!important; font-size:inherit!important; font-family:inherit!important; font-weight:inherit!important; line-height:inherit!important;}
.es-desk-hidden { display:none; float:left; overflow:hidden; width:0; max-height:0; line-height:0; mso-hide:all;}@media only screen and (max-width:600px) {.es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-p-default { } *[class="gmail-fix"] { display:none!important } p, a { line-height:150%!important } h1, h1 a { line-height:120%!important } h2, h2 a { line-height:120%!important } h3, h3 a { line-height:120%!important } h4, h4 a { line-height:120%!important } h5, h5 a { line-height:120%!important } h6, h6 a { line-height:120%!important } .es-header-body p { } .es-content-body p { } .es-footer-body p { } .es-infoblock p { } h1 { font-size:36px!important; text-align:left } h2 { font-size:26px!important; text-align:left } h3 { font-size:20px!important; text-align:left } h4 { font-size:24px!important; text-align:left } h5 { font-size:20px!important; text-align:left }
 h6 { font-size:16px!important; text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:36px!important } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:26px!important } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:20px!important } .es-header-body h4 a, .es-content-body h4 a, .es-footer-body h4 a { font-size:24px!important } .es-header-body h5 a, .es-content-body h5 a, .es-footer-body h5 a { font-size:20px!important } .es-header-body h6 a, .es-content-body h6 a, .es-footer-body h6 a { font-size:16px!important } .es-menu td a { font-size:12px!important } .es-header-body p, .es-header-body a { font-size:14px!important } .es-content-body p, .es-content-body a { font-size:14px!important } .es-footer-body p, .es-footer-body a { font-size:14px!important } .es-infoblock p, .es-infoblock a { font-size:12px!important }
 .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3, .es-m-txt-c h4, .es-m-txt-c h5, .es-m-txt-c h6 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3, .es-m-txt-r h4, .es-m-txt-r h5, .es-m-txt-r h6 { text-align:right!important } .es-m-txt-j, .es-m-txt-j h1, .es-m-txt-j h2, .es-m-txt-j h3, .es-m-txt-j h4, .es-m-txt-j h5, .es-m-txt-j h6 { text-align:justify!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3, .es-m-txt-l h4, .es-m-txt-l h5, .es-m-txt-l h6 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-m-txt-r .rollover:hover .rollover-second, .es-m-txt-c .rollover:hover .rollover-second, .es-m-txt-l .rollover:hover .rollover-second { display:inline!important }
 .es-m-txt-r .rollover span, .es-m-txt-c .rollover span, .es-m-txt-l .rollover span { line-height:0!important; font-size:0!important; display:block } .es-spacer { display:inline-table } a.es-button, button.es-button { font-size:20px!important; padding:10px 20px 10px 20px!important; line-height:120%!important } a.es-button, button.es-button, .es-button-border { display:inline-block!important } .es-m-fw, .es-m-fw.es-fw, .es-m-fw .es-button { display:block!important } .es-m-il, .es-m-il .es-button, .es-social, .es-social td, .es-menu { display:inline-block!important } .es-adaptive table, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .adapt-img { width:100%!important; height:auto!important } .es-mobile-hidden, .es-hidden { display:none!important }
 .es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } .h-auto { height:auto!important } }@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }</style>
 </head> <body class="body" style="width:100%;height:100%;padding:0;Margin:0"><div dir="ltr" class="es-wrapper-color" lang="fr" style="background-color:#FAFAFA"> <!--[if gte mso 9]><v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t"> <v:fill type="tile" color="#fafafa"></v:fill> </v:background><![endif]--><table width="100%" cellspacing="0" cellpadding="0" class="es-wrapper" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#FAFAFA"><tr><td valign="top" style="padding:0;Margin:0"><table cellpadding="0" cellspacing="0" align="center" class="es-content" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important"><tr>
<td align="center" class="es-info-area" style="padding:0;Margin:0"><table align="center" cellpadding="0" cellspacing="0" bgcolor="#00000000" class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" role="none"><tr><td align="left" style="padding:20px;Margin:0"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="center" class="es-infoblock" style="padding:0;Margin:0"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#CCCCCC;font-size:12px"><a target="_blank" href="" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px">View online version</a></p> </td></tr></table></td></tr></table></td></tr></table></td></tr></table> <table cellpadding="0" cellspacing="0" align="center" class="es-header" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important;background-color:transparent;background-repeat:repeat;background-position:center top"><tr>
<td align="center" style="padding:0;Margin:0"><table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="es-header-body" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px"><tr><td align="left" style="padding:20px;Margin:0"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td valign="top" align="center" class="es-m-p0r" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="center" style="padding:0;Margin:0;font-size:0"><img src="https://flhhcbi.stripocdn.email/content/guids/CABINET_bc4b0cf2eb23cbd9d8fcc70979f49d334b8b3d575a903dd82d870c1f2cf0ee10/images/timepillslogo.png" alt="" width="95" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none"></td> </tr></table></td></tr></table></td></tr></table></td></tr></table> <table cellpadding="0" cellspacing="0" align="center" class="es-content" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important"><tr><td align="center" style="padding:0;Margin:0"><table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="es-content-body" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px"><tr>
<td align="left" style="padding:0;Margin:0;padding-top:15px;padding-right:20px;padding-left:20px"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px;font-size:0px"><img src="https://flhhcbi.stripocdn.email/content/guids/CABINET_bc4b0cf2eb23cbd9d8fcc70979f49d334b8b3d575a903dd82d870c1f2cf0ee10/images/image.png" alt="" width="250" class="adapt-img" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none"></td> </tr><tr>
<td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:15px;padding-right:40px;padding-bottom:15px;padding-left:40px"><h1 class="es-m-txt-c" style="Margin:0;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:46px;font-style:normal;font-weight:bold;line-height:55.2px;color:#FF5959">​C'est l'heure 💊​</h1></td></tr><tr><td align="left" style="padding:0;Margin:0;padding-top:10px"><p style="Margin:0;mso-line-height-rule:exactly;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;letter-spacing:0;color:#333333;font-size:14px">Il est l'heure de prendre un de vos traitements !</p><ol style="font-family:arial, 'helvetica neue', helvetica, sans-serif;padding:0px 0px 0px 40px;margin:15px 0px">
<li style="color:#333333;margin:0px 0px 15px;font-size:14px;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif"><span style="font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif">Cliquez sur le bouton ci-dessous, et connectez vous si nécessaire</span></li> <li style="color:#333333;margin:0px 0px 15px;font-size:14px;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif"><span style="font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif">Cliquez sur le bouton "Prendre" et prenez votre traitement</span></li> <li style="color:#333333;margin:0px 0px 15px;font-size:14px;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif"><span style="font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif">Et voilà ! Vous avez pris votre traitement, sans l'oublier, et avez rempli votre calendrier !</span></li> </ol></td></tr></table></td></tr></table> </td></tr> <tr>
<td align="left" style="padding:0;Margin:0;padding-right:20px;padding-left:20px;padding-bottom:20px"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-radius:5px" role="presentation"><tr>
<td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px"><span class="es-button-border" style="border-style:solid;border-color:#2CB543;background:#FF5959;border-width:0px;display:inline-block;border-radius:6px;width:auto"><a href="https://timepills.thomasloye.fr/utilisateur/medicaments" target="_blank" class="es-button" style="mso-style-priority:100 !important;text-decoration:none !important;mso-line-height-rule:exactly;color:#FFFFFF;font-size:20px;padding:10px 30px 10px 30px;display:inline-block;background:#FF5959;border-radius:6px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:24px;width:auto;text-align:center;letter-spacing:0;mso-padding-alt:0;mso-border-alt:10px solid #FF5959;border-left-width:30px;border-right-width:30px">PRENDRE VOTRE TRAITEMENT</a> </span></td></tr><tr>
<td align="center" style="padding:0;Margin:0;padding-top:10px"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:bold;line-height:30px;color:#333333">Si vous ne prenez pas votre traitement, un rappel vous sera renvoyé dans 10 minutes.</h3></td></tr><tr><td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;letter-spacing:0;color:#333333;font-size:14px">Pensez à prendre votre traitement pour stopper l'envoi de mails sur votre messagerie.</p></td></tr></table></td></tr></table></td></tr></table></td></tr></table>
 <table cellpadding="0" cellspacing="0" align="center" class="es-footer" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important;background-color:transparent;background-repeat:repeat;background-position:center top"><tr><td align="center" style="padding:0;Margin:0"><table align="center" cellpadding="0" cellspacing="0" class="es-footer-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" role="none"><tr><td align="left" style="Margin:0;padding-right:20px;padding-left:20px;padding-bottom:20px;padding-top:20px"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="left" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" style="padding:0;Margin:0;padding-bottom:35px"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#333333;font-size:12px"><br></p> <p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#333333;font-size:12px">TimePills © 2024 Thomas Loye - thomasloye.fr</p><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#333333;font-size:12px">99 avenue d'Occitanie, 34000 Montpellier - FRANCE</p></td></tr></table></td></tr>
</table></td></tr></table></td></tr></table> <table cellpadding="0" cellspacing="0" align="center" class="es-content" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important"><tr><td align="center" class="es-info-area" style="padding:0;Margin:0"><table align="center" cellpadding="0" cellspacing="0" bgcolor="#00000000" class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" role="none"><tr><td align="left" style="padding:20px;Margin:0"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" class="es-infoblock" style="padding:0;Margin:0"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#CCCCCC;font-size:12px"><a target="_blank" href="" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px"></a>No longer want to receive these emails?&nbsp;<a href="" target="_blank" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px">Unsubscribe</a>.<a target="_blank" href="" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px"></a></p> </td></tr></table></td></tr></table>
</td></tr></table></td></tr></table></td></tr></table></div></body></html>
HTML;
    }


    private function getHtmlMessageReminder()
    {
        return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="fr"><head><meta charset="UTF-8"><meta content="width=device-width, initial-scale=1" name="viewport"><meta name="x-apple-disable-message-reformatting"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta content="telephone=no" name="format-detection"><title>New Message</title> <!--[if (mso 16)]><style type="text/css">     a {text-decoration: none;}     </style><![endif]--> <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--> <!--[if gte mso 9]><noscript> <xml> <o:OfficeDocumentSettings> <o:AllowPNG></o:AllowPNG> <o:PixelsPerInch>96</o:PixelsPerInch> </o:OfficeDocumentSettings> </xml> </noscript>
<![endif]--> <!--[if mso]><style type="text/css">    ul {   margin: 0 !important; } ol {   margin: 0 !important; } li {   margin-left: 47px !important; }  </style>
<![endif] --> <!--[if !mso]><!-- --><link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet"><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i"> <!--<![endif]--><style type="text/css">.rollover:hover .rollover-first { max-height:0px!important; display:none!important;}.rollover:hover .rollover-second { max-height:none!important; display:block!important;}.rollover span { font-size:0px;}u + .body img ~ div div { display:none;}#outlook a { padding:0;}span.MsoHyperlink,span.MsoHyperlinkFollowed { color:inherit; mso-style-priority:99;} a.es-button { mso-style-priority:100!important; text-decoration:none!important;}a[x-apple-data-detectors],#MessageViewBody a { color:inherit!important; text-decoration:none!important; font-size:inherit!important; font-family:inherit!important; font-weight:inherit!important; line-height:inherit!important;}
.es-desk-hidden { display:none; float:left; overflow:hidden; width:0; max-height:0; line-height:0; mso-hide:all;}@media only screen and (max-width:600px) {.es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-p-default { } *[class="gmail-fix"] { display:none!important } p, a { line-height:150%!important } h1, h1 a { line-height:120%!important } h2, h2 a { line-height:120%!important } h3, h3 a { line-height:120%!important } h4, h4 a { line-height:120%!important } h5, h5 a { line-height:120%!important } h6, h6 a { line-height:120%!important } .es-header-body p { } .es-content-body p { } .es-footer-body p { } .es-infoblock p { } h1 { font-size:36px!important; text-align:left } h2 { font-size:26px!important; text-align:left } h3 { font-size:20px!important; text-align:left } h4 { font-size:24px!important; text-align:left } h5 { font-size:20px!important; text-align:left }
 h6 { font-size:16px!important; text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:36px!important } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:26px!important } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:20px!important } .es-header-body h4 a, .es-content-body h4 a, .es-footer-body h4 a { font-size:24px!important } .es-header-body h5 a, .es-content-body h5 a, .es-footer-body h5 a { font-size:20px!important } .es-header-body h6 a, .es-content-body h6 a, .es-footer-body h6 a { font-size:16px!important } .es-menu td a { font-size:12px!important } .es-header-body p, .es-header-body a { font-size:14px!important } .es-content-body p, .es-content-body a { font-size:14px!important } .es-footer-body p, .es-footer-body a { font-size:14px!important } .es-infoblock p, .es-infoblock a { font-size:12px!important }
 .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3, .es-m-txt-c h4, .es-m-txt-c h5, .es-m-txt-c h6 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3, .es-m-txt-r h4, .es-m-txt-r h5, .es-m-txt-r h6 { text-align:right!important } .es-m-txt-j, .es-m-txt-j h1, .es-m-txt-j h2, .es-m-txt-j h3, .es-m-txt-j h4, .es-m-txt-j h5, .es-m-txt-j h6 { text-align:justify!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3, .es-m-txt-l h4, .es-m-txt-l h5, .es-m-txt-l h6 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-m-txt-r .rollover:hover .rollover-second, .es-m-txt-c .rollover:hover .rollover-second, .es-m-txt-l .rollover:hover .rollover-second { display:inline!important }
 .es-m-txt-r .rollover span, .es-m-txt-c .rollover span, .es-m-txt-l .rollover span { line-height:0!important; font-size:0!important; display:block } .es-spacer { display:inline-table } a.es-button, button.es-button { font-size:20px!important; padding:10px 20px 10px 20px!important; line-height:120%!important } a.es-button, button.es-button, .es-button-border { display:inline-block!important } .es-m-fw, .es-m-fw.es-fw, .es-m-fw .es-button { display:block!important } .es-m-il, .es-m-il .es-button, .es-social, .es-social td, .es-menu { display:inline-block!important } .es-adaptive table, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .adapt-img { width:100%!important; height:auto!important } .es-mobile-hidden, .es-hidden { display:none!important }
 .es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } .h-auto { height:auto!important } }@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }</style>
 </head> <body class="body" style="width:100%;height:100%;padding:0;Margin:0"><div dir="ltr" class="es-wrapper-color" lang="fr" style="background-color:#FAFAFA"> <!--[if gte mso 9]><v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t"> <v:fill type="tile" color="#fafafa"></v:fill> </v:background><![endif]--><table width="100%" cellspacing="0" cellpadding="0" class="es-wrapper" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#FAFAFA"><tr><td valign="top" style="padding:0;Margin:0"><table cellpadding="0" cellspacing="0" align="center" class="es-content" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important"><tr>
<td align="center" class="es-info-area" style="padding:0;Margin:0"><table align="center" cellpadding="0" cellspacing="0" bgcolor="#00000000" class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" role="none"><tr><td align="left" style="padding:20px;Margin:0"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="center" class="es-infoblock" style="padding:0;Margin:0"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#CCCCCC;font-size:12px"><a target="_blank" href="" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px">View online version</a></p> </td></tr></table></td></tr></table></td></tr></table></td></tr></table> <table cellpadding="0" cellspacing="0" align="center" class="es-header" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important;background-color:transparent;background-repeat:repeat;background-position:center top"><tr>
<td align="center" style="padding:0;Margin:0"><table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="es-header-body" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px"><tr><td align="left" style="padding:20px;Margin:0"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td valign="top" align="center" class="es-m-p0r" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="center" style="padding:0;Margin:0;font-size:0"><img src="https://flhhcbi.stripocdn.email/content/guids/CABINET_bc4b0cf2eb23cbd9d8fcc70979f49d334b8b3d575a903dd82d870c1f2cf0ee10/images/timepillslogo.png" alt="" width="95" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none"></td> </tr></table></td></tr></table></td></tr></table></td></tr></table> <table cellpadding="0" cellspacing="0" align="center" class="es-content" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important"><tr><td align="center" style="padding:0;Margin:0"><table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="es-content-body" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px"><tr>
<td align="left" style="padding:0;Margin:0;padding-top:15px;padding-right:20px;padding-left:20px"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px;font-size:0px"><img src="https://flhhcbi.stripocdn.email/content/guids/CABINET_bc4b0cf2eb23cbd9d8fcc70979f49d334b8b3d575a903dd82d870c1f2cf0ee10/images/image.png" alt="" width="250" class="adapt-img" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none"></td> </tr><tr>
<td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:15px;padding-right:40px;padding-bottom:15px;padding-left:40px"><h1 class="es-m-txt-c" style="Margin:0;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:46px;font-style:normal;font-weight:bold;line-height:55.2px;color:#FF5959">RAPPEL : prenez votre traitement !💊​</h1></td></tr><tr><td align="left" style="padding:0;Margin:0;padding-top:10px"><p style="Margin:0;mso-line-height-rule:exactly;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;letter-spacing:0;color:#333333;font-size:14px">Il est l'heure de prendre un de vos traitements !</p><ol style="font-family:arial, 'helvetica neue', helvetica, sans-serif;padding:0px 0px 0px 40px;margin:15px 0px">
<li style="color:#333333;margin:0px 0px 15px;font-size:14px;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif"><span style="font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif">Cliquez sur le bouton ci-dessous, et connectez vous si nécessaire</span></li> <li style="color:#333333;margin:0px 0px 15px;font-size:14px;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif"><span style="font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif">Cliquez sur le bouton "Prendre" et prenez votre traitement</span></li> <li style="color:#333333;margin:0px 0px 15px;font-size:14px;font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif"><span style="font-family:roboto, 'helvetica neue', helvetica, arial, sans-serif">Et voilà ! Vous avez pris votre traitement, sans l'oublier, et avez rempli votre calendrier !</span></li> </ol></td></tr></table></td></tr></table> </td></tr> <tr>
<td align="left" style="padding:0;Margin:0;padding-right:20px;padding-left:20px;padding-bottom:20px"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-radius:5px" role="presentation"><tr>
<td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px"><span class="es-button-border" style="border-style:solid;border-color:#2CB543;background:#FF5959;border-width:0px;display:inline-block;border-radius:6px;width:auto"><a href="https://timepills.thomasloye.fr/utilisateur/medicaments" target="_blank" class="es-button" style="mso-style-priority:100 !important;text-decoration:none !important;mso-line-height-rule:exactly;color:#FFFFFF;font-size:20px;padding:10px 30px 10px 30px;display:inline-block;background:#FF5959;border-radius:6px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:24px;width:auto;text-align:center;letter-spacing:0;mso-padding-alt:0;mso-border-alt:10px solid #FF5959;border-left-width:30px;border-right-width:30px">PRENDRE VOTRE TRAITEMENT</a> </span></td></tr><tr>
<td align="center" style="padding:0;Margin:0;padding-top:10px"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:bold;line-height:30px;color:#333333">Si vous ne prenez pas votre traitement, un rappel vous sera renvoyé dans 10 minutes.</h3></td></tr><tr><td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;letter-spacing:0;color:#333333;font-size:14px">Pensez à prendre votre traitement pour stopper l'envoi de mails sur votre messagerie.</p></td></tr></table></td></tr></table></td></tr></table></td></tr></table>
 <table cellpadding="0" cellspacing="0" align="center" class="es-footer" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important;background-color:transparent;background-repeat:repeat;background-position:center top"><tr><td align="center" style="padding:0;Margin:0"><table align="center" cellpadding="0" cellspacing="0" class="es-footer-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" role="none"><tr><td align="left" style="Margin:0;padding-right:20px;padding-left:20px;padding-bottom:20px;padding-top:20px"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="left" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" style="padding:0;Margin:0;padding-bottom:35px"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#333333;font-size:12px"><br></p> <p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#333333;font-size:12px">TimePills © 2024 Thomas Loye - thomasloye.fr</p><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#333333;font-size:12px">99 avenue d'Occitanie, 34000 Montpellier - FRANCE</p></td></tr></table></td></tr>
</table></td></tr></table></td></tr></table> <table cellpadding="0" cellspacing="0" align="center" class="es-content" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%;table-layout:fixed !important"><tr><td align="center" class="es-info-area" style="padding:0;Margin:0"><table align="center" cellpadding="0" cellspacing="0" bgcolor="#00000000" class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" role="none"><tr><td align="left" style="padding:20px;Margin:0"><table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr>
<td align="center" valign="top" style="padding:0;Margin:0;width:560px"><table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"><tr><td align="center" class="es-infoblock" style="padding:0;Margin:0"><p style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;letter-spacing:0;color:#CCCCCC;font-size:12px"><a target="_blank" href="" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px"></a>No longer want to receive these emails?&nbsp;<a href="" target="_blank" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px">Unsubscribe</a>.<a target="_blank" href="" style="mso-line-height-rule:exactly;text-decoration:underline;color:#CCCCCC;font-size:12px"></a></p> </td></tr></table></td></tr></table>
</td></tr></table></td></tr></table></td></tr></table></div></body></html>
HTML;

    }
}