<?php

namespace App\Controller;

use App\Entity\Pilule;
use App\Form\PiluleType;
use App\Repository\UtilisateurRepository;
use App\Service\FlashMessageHelperInterface;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PiluleController extends AbstractController
{
    public function __construct(
        private FlashMessageHelperInterface $flashMessageHelperInterface,
        //private UtilisateurManagerInterface $utilisateurManagerInterface,
        //private UtilisateurRepository       $utilisateurRepository
    )
    {
    }


    #[Route('/creerPilule', name: 'creerPilule', methods: ['GET', 'POST'])]
    public function creerPilule(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PiluleType::class, new Pilule(), ['method' => 'POST', 'action' => $this->generateUrl('medicaments')]);
        $form->handleRequest($request);
        $this->flashMessageHelperInterface->addFormErrorsAsFlash($form);

        if ($request->isMethod('POST')) {
            $this->denyAccessUnlessGranted('ROLE_USER');

            $utilisateur = $this->getUser();

            if ($form->isSubmitted() && $form->isValid()) {
                $pilule = $form->getData();
                $pilule->setProprietaire($utilisateur);
                $entityManager->persist($pilule);
                $entityManager->flush();
                $this->addFlash('success', 'Pilule créée avec succès');
                return $this->redirectToRoute('medicaments');
            }
        }

        return $this->render('pilule/piluleform.html.twig', [
            'formPilule' => $form,
            'page_actuelle' => 'Medicaments'
        ]);
    }
}
