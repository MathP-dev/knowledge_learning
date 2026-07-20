<?php

namespace App\Controller\Payment;

use App\Service\Payment\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class ClearCartController extends AbstractController
{
    public function __invoke(Request $request, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('cart_clear', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('info', 'Votre panier est déjà vide.');
        } else {
            $cartService->clearCart($cart);
            $this->addFlash('success', 'Votre panier a été vidé.');
        }

        return $this->redirectToRoute('app_cart_index');
    }
}
