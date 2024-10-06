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
use Symfony\Component\Serializer\SerializerInterface;

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
        $form = $this->createForm(PiluleType::class, new Pilule(), ['method' => 'POST', 'action' => $this->generateUrl('creerPilule')]);
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


    #[\Symfony\Component\Routing\Annotation\Route('/infosPilule', name: 'infosPilule', options: ["expose" => true], methods: ['POST'])]
    public function infosPilule(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $idPilule = $request->get('idPilule');
        $pilule = $entityManager->getRepository(Pilule::class)->find($idPilule);

        $data = $serializer->serialize($pilule, 'json', ['groups' => 'pilule:read']);
        dump($data);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }
}
