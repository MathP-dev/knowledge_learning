<?php

namespace App\Service\Payment;

use App\Entity\Cart;
use App\Entity\User;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class StripeService
{
    public function __construct(
        private ParameterBagInterface $params,
    ) {
        Stripe::setApiKey($this->params->get('stripe.secret_key'));
    }

    public function getPublicKey(): string
    {
        return (string) $this->params->get('stripe.public_key');
    }

    /**
     * @param array<int, array{label: string, price: int}> $items Prix en centimes
     */
    public function createCartCheckoutSession(
        User $user,
        Cart $cart,
        array $items,
        string $successUrl,
        string $cancelUrl,
    ): Session {
        if ($items === []) {
            throw new \LogicException('Votre panier est vide.');
        }

        $lineItems = array_map(
            static fn (array $item): array => [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['label'],
                    ],
                    'unit_amount' => $item['price'],
                ],
                'quantity' => 1,
            ],
            $items,
        );

        $metadata = [
            'user_id' => (string) $user->getId(),
            'cart_id' => (string) $cart->getId(),
        ];
        try {
            $session = Session::create([
                'mode' => 'payment',
                'line_items' => $lineItems,
                'customer_email' => $user->getEmail(),
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => $metadata,
                'payment_intent_data' => [
                    'description' => 'Paiement du panier Knowledge Learning',
                    'metadata' => $metadata,
                ],
            ]);
            return $session;
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Erreur Stripe: ' . $e->getMessage());
        }
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return Session::retrieve([
            'id' => $sessionId,
            'expand' => ['payment_intent'],
        ]);
    }
}
