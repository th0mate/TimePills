<?php

namespace App\Controller;

use App\Entity\DatePrise;
use App\Entity\Pilule;
use App\Form\PiluleType;
use App\Repository\RappelRepository;
use App\Repository\UtilisateurRepository;
use App\Service\FlashMessageHelperInterface;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        private RappelRepository $rappelRepository
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


    #[Route('/infosPilule', name: 'infosPilule', options: ["expose" => true], methods: ['POST'])]
    public function infosPilule(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $idPilule = $request->get('idPilule');
        $pilule = $entityManager->getRepository(Pilule::class)->find($idPilule);
        $datesPrises = $pilule->getDatesPrises();

        $data = $serializer->serialize($pilule, 'json', ['groups' => 'pilule:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/prisesPilules', name: 'prisesPilule', options: ["expose" => true], methods: ['POST'])]
    public function prisesPilule(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $idPilule = $request->get('idPilule');
        $pilule = $entityManager->getRepository(Pilule::class)->find($idPilule);
        $datesPrises = $pilule->getDatesPrises()->toArray();
        dump($datesPrises);

        $data = $serializer->serialize($datesPrises, 'json', ['groups' => 'pilule:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/prendrePilule', name: 'prendrePilule', options: ["expose" => true], methods: ['POST'])]
    public function prendrePilule(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $idPilule = $request->get('idPilule');
        $pilule = $entityManager->getRepository(Pilule::class)->find($idPilule);

        $datePrise = new DatePrise();
        $datePrise->setDatePrise(new \DateTime());
        $pilule->addDatePrise($datePrise);

        $rappel = $this->rappelRepository->findBy(['idPilule' => $idPilule]);
        dump($rappel);

        if (count($rappel) > 0)
            $this->rappelRepository->delete($rappel[0]);


        $entityManager->persist($datePrise);
        $entityManager->flush();

        $this->addFlash('success', 'Traitement prise avec succès !');
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/piluleEstEnPause', name: 'piluleEstEnPause', options: ["expose" => true], methods: ['POST'])]
    public function piluleEstEnPause(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $idPilule = $request->get('idPilule');
        dump($idPilule);
        $pilule = $entityManager->getRepository(Pilule::class)->find($idPilule);

        $estEnPause = $pilule->estEnPause();

        return new JsonResponse($estEnPause, Response::HTTP_OK);
    }

}
