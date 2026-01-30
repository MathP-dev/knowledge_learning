<?php

namespace App\Controller\Auth;

use App\Service\Auth\VerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/verification/{token}', name: 'app_verify_email')]
class VerifyEmailController extends AbstractController
{
    public function __construct(
        private readonly VerificationService $verificationService
    ) {
    }

    public function __invoke(string $token): Response
    {
        $user = $this->verificationService->verifyUser($token);

        if (!$user) {
            $this->addFlash('error', 'Le lien de vérification est invalide ou a expiré.');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}
