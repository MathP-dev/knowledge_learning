<?php

namespace App\Controller\Auth;

use App\DTO\RegistrationDTO;
use App\Form\RegistrationType;
use App\Service\Auth\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/register', name: 'app_register')]
class RegisterController extends AbstractController
{

    public function __invoke(Request $request, RegistrationService $registrationService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $dto = new RegistrationDTO();
        $form = $this->createForm(RegistrationType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $registrationService->register($dto);
                $this->addFlash('success', 'Votre compte a été créé ! Veuillez vérifier votre email pour activer votre compte.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription.');
            }
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form,
        ]);
    }
}

