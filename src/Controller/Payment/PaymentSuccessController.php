<?php

namespace App\Controller\Payment;

use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Repository\UserRepository;
use App\Service\Payment\PurchaseService;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment/success', name: 'app_payment_success')]
#[IsGranted('ROLE_USER')]
class PaymentSuccessController extends AbstractController
{
    public function __construct(
        private PurchaseService $purchaseService,
        private UserRepository $userRepository,
        private CourseRepository $courseRepository,
        private LessonRepository $lessonRepository,
        private ParameterBagInterface $params
    ) {
        Stripe::setApiKey($this->params->get('stripe.secret_key'));
    }

    public function __invoke(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement introuvable.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $session = Session::retrieve($sessionId);
            $metadata = $session->metadata;

            $user = $this->userRepository->find($metadata['user_id']);
            $type = $metadata['type'];

            $course = null;
            $lesson = null;

            if ($type === 'course') {
                $course = $this->courseRepository->find($metadata['course_id']);
            } elseif ($type === 'lesson') {
                $lesson = $this->lessonRepository->find($metadata['lesson_id']);
            }

            $this->purchaseService->createPurchase(
                $user,
                $session->payment_intent,
                (string) ($session->amount_total / 100),
                $course,
                $lesson
            );

            return $this->render('payment/success.html.twig', [
                'course' => $course,
                'lesson' => $lesson,
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors du traitement de votre paiement.');
            return $this->redirectToRoute('app_home');
        }
    }
}
