<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/', name: 'TimePills', options: ["expose" => true], methods: ['GET', 'POST'])]
    public function index(): Response
    {
        return $this->render('accueil.html.twig', ['page_actuelle' => 'Accueil']);
    }
}
