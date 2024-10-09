<?php

namespace App\Controller;

use App\Entity\OneSignalId;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\FlashMessageHelperInterface;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UtilisateurController extends AbstractController
{

    public function __construct(
        private FlashMessageHelperInterface $flashMessageHelperInterface,
        private UtilisateurManagerInterface $utilisateurManagerInterface,
        private UtilisateurRepository $utilisateurRepository
    ) {
    }

    #[Route('/', name: 'TimePills', options: ["expose" => true], methods: ['GET', 'POST'])]
    public function index(): Response
    {
        return $this->render('accueil.html.twig', ['page_actuelle' => 'Accueil']);
    }

    /**
     * Route pour afficher les crédits
     * @return Response
     */
    #[Route('/credits', name: 'credits', methods: 'GET')]
    public function afficherCredits(): Response
    {
        return $this->render('credits/credits.html.twig', ['page_actuelle' => 'Credits']);
    }

    /**
     * Route pour s'inscrire
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, EntityManagerInterface $entityManager): Response
    {

        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('TimePills');
        }

        $inscription = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $inscription, [
            'method' => 'POST',
            'action' => $this->generateURL('inscription')
        ]);
        $form->handleRequest($request);
        $this->flashMessageHelperInterface->addFormErrorsAsFlash($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData();
            $this->utilisateurManagerInterface->processNewUtilisateur($utilisateur, $form->get('plainPassword')->getData());
            $entityManager->persist($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', 'Profil inscrit avec succès !');
            return $this->redirectToRoute('TimePills');
        }

        return $this->render('utilisateur/inscription.html.twig', ['formInscription' => $form, 'page_actuelle' => 'Inscription']);
    }

    /**
     * Route pour se connecter
     * @param AuthenticationUtils $authenticationUtils
     * @return Response : page de connexion
     */
    #[Route('/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            $this->addFlash('danger', 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('TimePills');
        }

        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('utilisateur/connexion.html.twig', ['page_actuelle' => 'Connexion', 'last_username' => $lastUsername]);
    }

    /**
     * Route exposée permettant de vérifier si une adresse mail est déjà prise dans la bd ou non
     * @param Request $request
     * @return Response : true si l'adresse mail n'est pas prise, false sinon
     */
    #[Route('/verifier_email', name: 'verifier_email', options: ["expose" => true], methods: ['POST'])]
    public function verifierEmail(Request $request): Response
    {
        $email = $request->get("adresseMail");
        $utilisateur = $this->utilisateurRepository->findOneBy(['adresseMail' => $email]);

        if ($utilisateur) {
            return new Response('true');
        }
        return new Response('false');
    }

    /**
     * Route pour afficher la page de ses médicaments
     */
    #[Route('utilisateur/medicaments', name: 'medicaments', methods: ['GET', 'POST'])]
    public function afficherMedicaments(): Response
    {
        $medicaments = $this->getUser()->getPilules();
        return $this->render('utilisateur/medicaments.html.twig', ['page_actuelle' => 'Medicaments', 'medicaments' => $medicaments]);
    }

    #[Route('utilisateur/pilules', name: 'listeIdPilules', options: ["expose" => true], methods: ['POST'])]
    public function getIdPilulesUtilisateur(): Response
    {
        $pilules = $this->getUser()->getPilules();
        $idPilules = [];
        foreach ($pilules as $pilule) {
            $idPilules[] = $pilule->getId();
        }
        return $this->json($idPilules);
    }

    #[Route('utilisateur/changerNotification', name: 'changerNotification', options: ["expose" => true], methods: ['POST'])]
    public function changerNotification(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bool = $request->get('veutNotification');
        $utilisateur = $this->getUser();
        $utilisateur->setVeutNotification($bool);
        $entityManager->persist($utilisateur);
        $entityManager->flush();
        return new Response('true');
    }

    #[Route('/utilisateur/enregistrerOneSignalId', name: 'enregistrer_one_signal_id', methods: ['POST'], options: ["expose" => true])]
    public function enregistrerOneSignalId(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $oneSignalIdValue = $request->get('oneSignalId');
        $utilisateur = $this->getUser();

        if (!$utilisateur) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $oneSignalId = $entityManager->getRepository(OneSignalId::class)->findOneBy(['oneSignalId' => $oneSignalIdValue]);
        if ($oneSignalId) {
            return new JsonResponse(['error' => 'OneSignal ID already registered']);
        }

        $oneSignalId = new OneSignalId();
        $oneSignalId->setOneSignalId($oneSignalIdValue);
        $oneSignalId->setUtilisateur($utilisateur);

        $entityManager->persist($oneSignalId);
        $entityManager->flush();

        return new JsonResponse(['success' => 'OneSignal ID registered successfully']);
    }
}
