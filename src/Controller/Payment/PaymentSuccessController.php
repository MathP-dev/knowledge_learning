<?php

namespace App\Controller\Payment;

use App\Service\Payment\StripeService;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment/success', name: 'app_payment_success')]
#[IsGranted('ROLE_USER')]
class PaymentSuccessController extends AbstractController
{
    public function __invoke(Request $request, StripeService $stripeService): Response
    {
        $sessionId = (string) $request->query->get('session_id', '');

        if ($sessionId === '') {
            $this->addFlash('error', 'Paiement introuvable.');
            return $this->redirectToRoute('app_cart_index');
        }

        try {
            $session = $stripeService->retrieveCheckoutSession($sessionId);
        } catch (ApiErrorException $e) {
            $this->addFlash('error', 'Impossible de vérifier votre paiement.');
            return $this->redirectToRoute('app_cart_index');
        }

        if ($session->payment_status !== 'paid') {
            $this->addFlash('error', 'Le paiement n’a pas été validé.');
            return $this->redirectToRoute('app_cart_index');
        }

        $user = $this->getUser();
        $metadata = $session->metadata;
        $cart = $user->getCart();

        if (
            !$cart ||
            !isset($metadata->user_id, $metadata->cart_id) ||
            (int) $metadata->user_id !== $user->getId() ||
            (int) $metadata->cart_id !== $cart->getId()
        ) {
            $this->addFlash('error', 'Les données du paiement sont invalides.');
            return $this->redirectToRoute('app_cart_index');
        }

        $this->addFlash('success', 'Votre paiement a été effectué avec succès !');

        return $this->render('payment/success.html.twig');
    }
}
