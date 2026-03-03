<?php

namespace App\Controller\Auth;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/connexion', name: 'app_login')]
class LoginController extends AbstractController
{
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class, [
            'email' => $lastUsername
        ]);

        return $this->render('auth/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }
}
