<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PiluleController extends AbstractController
{
    #[Route('/pilule', name: 'app_pilule')]
    public function index(): Response
    {
        return $this->render('pilule/index.html.twig', [
            'controller_name' => 'PiluleController',
        ]);
    }
}
