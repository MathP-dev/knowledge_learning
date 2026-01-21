<?php

namespace App\Controller\Payment;

use App\Service\Course\LessonService;
use App\Service\Payment\PurchaseService;
use App\Service\Payment\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lecon/{slug}/acheter', name: 'app_lesson_buy')]
#[IsGranted('ROLE_USER')]
class BuyLessonController extends AbstractController
{
    public function __construct(
        private LessonService $lessonService,
        private StripeService $stripeService,
        private PurchaseService $purchaseService
    ) {
    }

    public function __invoke(string $slug): Response
    {
        $lesson = $this->lessonService->getLessonBySlug($slug);

        if (!$lesson) {
            throw $this->createNotFoundException('Cette leçon n\'existe pas.');
        }

        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte avant de pouvoir acheter une leçon.');
            return $this->redirectToRoute('app_lesson_show', ['slug' => $slug]);
        }

        if ($this->purchaseService->hasUserPurchasedLesson($user, $lesson)) {
            $this->addFlash('info', 'Vous avez déjà accès à cette leçon.');
            return $this->redirectToRoute('app_lesson_show', ['slug' => $slug]);
        }

        $session = $this->stripeService->createCheckoutSession($user, lesson: $lesson);

        return $this->redirect($session->url);
    }
}