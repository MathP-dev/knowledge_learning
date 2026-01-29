<?php

namespace App\Controller\Admin;

use App\Service\Course\CourseService;
use App\Service\Payment\PurchaseService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'app_admin_dashboard')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly UserService   $userService,
        private readonly CourseService $courseService,
        private readonly PurchaseService $purchaseService
    ) {
    }

    public function __invoke(): Response
    {
        $users = $this->userService->getAllUsers();
        $courses = $this->courseService->getAllCourses();
        $purchases = $this->purchaseService->getAllPurchases();

        return $this->render('admin/admin_dashboard.html.twig', [
            'users' => $users,
            'courses' => $courses,
            'purchases' => $purchases,
        ]);
    }
}
