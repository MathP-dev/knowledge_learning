<?php

namespace App\Controller\Payment;

use App\Entity\CartItem;
use App\Service\Payment\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart/remove/{id}', name: 'app_cart_remove_item', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class RemoveItemController extends AbstractController
{
    public function __invoke(Request $request, CartItem $cartItem, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('cart_remove_item' . $cartItem->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CRSF invalide.');
        }

        $user = $this->getUser();
        $cart = $user->getCart();

        if (!$cart || $cartItem->getCart()->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $cartService->removeItem($cart, $cartItem);
        $this->addFlash('success', 'Élément retiré du panier');

        return $this->redirectToRoute('app_cart_index');
    }
}
