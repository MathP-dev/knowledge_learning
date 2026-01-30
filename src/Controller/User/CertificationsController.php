<?php

namespace App\Controller\User;

use App\Service\User\CertificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mes-certifications', name: 'app_user_certifications')]
#[IsGranted('ROLE_USER')]
class CertificationsController extends AbstractController
{
    public function __construct(
        private readonly CertificationService $certificationService
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->getUser();
        $certifications = $this->certificationService->getUserCertifications($user);

        return $this->render('user/certifications.html.twig', [
            'certifications' => $certifications,
        ]);
    }
}
