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
        private readonly UserService     $userService,
        private readonly CourseService   $courseService,
        private readonly PurchaseService $purchaseService
    ) {
    }

    public function __invoke(): Response
    {
        $users = $this->userService->getAllUsers();
        $courses = $this->courseService->getAllCourses();
        $purchases = $this->purchaseService->getAllPurchases();

        $stats = [
            'total_revenue' => $this->calculateTotalRevenue($purchases),
            'verified_users' => $this->countVerifiedUsers($users),
            'recent_purchases' => array_slice($purchases, 0, 10),
        ];

        return $this->render('admin/admin_dashboard.html.twig', [
            'users' => $users,
            'courses' => $courses,
            'purchases' => $purchases,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate the total purchase revenue
     */
    private function calculateTotalRevenue(array $purchases): float
    {
        return array_reduce($purchases, function ($total, $purchase) {
            return $total + (float) $purchase->getAmount();
        }, 0.0);
    }

    /**
     * Counts the number of verified users
     */
    private function countVerifiedUsers(array $users): int
    {
        return count(array_filter($users, function ($user) {
            return $user->isVerified();
        }));
    }
}
