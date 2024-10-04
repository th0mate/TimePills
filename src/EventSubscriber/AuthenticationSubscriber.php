<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthenticationSubscriber
{

    public function __construct(private RequestStack $requestStack)
    {
    }

    #[AsEventListener]
    public function connexionReussie(LoginSuccessEvent $event): void
    {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add('success', 'Connexion Réussie !');
    }

    #[AsEventListener]
    public function connexionEchouee(LoginFailureEvent $event): void
    {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add('warning', 'Login et/ou mot de passe incorrect !');
    }

    #[AsEventListener]
    public function deconnexionReussie(LogoutEvent $event):void
    {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add('info', 'Vous êtes déconnecté');
    }


}