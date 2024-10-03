<?php


namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FlashMessageHelper implements FlashMessageHelperInterface
{

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function addFormErrorsAsFlash(FormInterface $form): void
    {
        $errors = $form->getErrors(true);
        foreach ($errors as $error) {
            $errorMsg = $error->getMessage();
            $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add('warning', $errorMsg);
        }
    }
}
