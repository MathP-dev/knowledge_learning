<?php

namespace App\Controller\User;

use App\Service\Payment\PurchaseService;
use App\Service\User\CertificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mon-compte', name: 'app_user_dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    public function __construct(
        private PurchaseService $purchaseService,
        private CertificationService $certificationService
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->getUser();
        $purchases = $this->purchaseService->getUserPurchases($user);
        $certifications = $this->certificationService->getUserCertifications($user);

        return $this->render('user/user_dashboard.html.twig', [
            'purchases' => $purchases,
            'certifications' => $certifications,
        ]);
    }
}
