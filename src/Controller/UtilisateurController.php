<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Service\FlashMessageHelperInterface;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{

    public function __construct(
        private FlashMessageHelperInterface $flashMessageHelperInterface,
        private UtilisateurManagerInterface $utilisateurManagerInterface
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



}
