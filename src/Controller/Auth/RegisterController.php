<?php

namespace App\Controller\Auth;

use App\DTO\RegistrationDTO;
use App\Service\Auth\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/register', name: 'app_register')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $dto = new RegistrationDTO();
        $errors = [];

        if ($request->isMethod('POST')) {
            $dto->firstName = $request->request->get('firstName');
            $dto->lastName = $request->request->get('lastName');
            $dto->email = $request->request->get('email');
            $dto->password = $request->request->get('password');
            $dto->confirmPassword = $request->request->get('confirmPassword');

            $violations = $this->validator->validate($dto);

            if (count($violations) === 0) {
                try {
                    $this->registrationService->register($dto);
                    $this->addFlash('success', 'Votre compte a été créé !  Veuillez vérifier votre email pour activer votre compte.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription.');
                }
            } else {
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'dto' => $dto,
            'errors' => $errors,
        ]);
    }
}
