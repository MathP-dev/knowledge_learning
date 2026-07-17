<?php

namespace App\Controller\Payment;

use App\Entity\CartItemType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\Payment\CartService;
use App\Service\Payment\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class CartCheckoutController extends AbstractController
{
    public function __invoke(
        CartService $cartService,
        StripeService $stripeService,
        LessonRepository $lessonRepository,
        CourseRepository $courseRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte pour effectuer un achat.');
            return $this->redirectToRoute('app_cart_index');
        }

        $cart = $cartService->getOrCreateCart($user);

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $items = [];
        foreach ($cart->getItems() as $item) {
            if ($item->getType() === CartItemType::LESSON) {
                $lesson = $lessonRepository->find($item->getLessonId());
                $label = $lesson?->getTitle() ?? 'Leçon #' . $item->getLessonId();
            } else {
                $course = $courseRepository->find($item->getCourseId());
                $label = $course?->getTitle() ?? 'Formation #' . $item->getCourseId();
            }

            $items[] = [
                'label' => $label,
                'price' => $item->getPrice(),
            ];
        }

        $successUrl = $this->generateUrl('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $this->generateUrl('app_cart_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            $session = $stripeService->createCartCheckoutSession($user, $cart, $items, $successUrl, $cancelUrl);
        } catch (\RuntimeException|\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_cart_index');
        }

        if ($session->url === null) {
            throw new \RuntimeException('URL de paiement Stripe introuvable.');
        }

        return $this->redirect($session->url);
    }
}
