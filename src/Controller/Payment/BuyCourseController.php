<?php

namespace App\Controller\Payment;

use App\Service\Course\CourseService;
use App\Service\Payment\PurchaseService;
use App\Service\Payment\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cursus/{slug}/acheter', name: 'app_course_buy')]
#[IsGranted('ROLE_USER')]
class BuyCourseController extends AbstractController
{
    public function __construct(
        private CourseService $courseService,
        private StripeService $stripeService,
        private PurchaseService $purchaseService
    ) {
    }

    public function __invoke(string $slug): Response
    {
        $course = $this->courseService->getCourseBySlug($slug);

        if (!$course) {
            throw $this->createNotFoundException('Ce cursus n\'existe pas.');
        }

        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte avant de pouvoir acheter un cursus.');
            return $this->redirectToRoute('app_course_show', ['slug' => $slug]);
        }

        if ($this->purchaseService->hasUserPurchasedCourse($user, $course)) {
            $this->addFlash('info', 'Vous avez déjà acheté ce cursus.');
            return $this->redirectToRoute('app_course_show', ['slug' => $slug]);
        }

        $session = $this->stripeService->createCheckoutSession($user, course:  $course);

        return $this->redirect($session->url);
    }
}