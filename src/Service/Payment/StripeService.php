<?php

namespace App\Service\Payment;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class StripeService
{
    public function __construct(
        private ParameterBagInterface $params,
        private UrlGeneratorInterface $urlGenerator,
        private PurchaseRepository    $purchaseRepository
    ) {
        Stripe::setApiKey($this->params->get('stripe.secret_key'));
    }

    public function createCheckoutSession(User $user, ? Course $course = null, ?Lesson $lesson = null): Session
    {
        $baseUrl = $this->params->get('site.base_url');

        if ($course) {
            $itemName = 'Cursus:  ' . $course->getTitle();
            $amount = (float) $course->getPrice();
            $successUrl = $baseUrl . $this->urlGenerator->generate('app_payment_success');
            $cancelUrl = $baseUrl . $this->urlGenerator->generate('app_payment_cancel');
            $metadata = ['type' => 'course', 'course_id' => $course->getId()];
        } elseif ($lesson) {
            $itemName = 'Leçon: ' . $lesson->getTitle();
            $amount = (float) $lesson->getPrice();
            $successUrl = $baseUrl . $this->urlGenerator->generate('app_payment_success');
            $cancelUrl = $baseUrl . $this->urlGenerator->generate('app_payment_cancel');
            $metadata = ['type' => 'lesson', 'lesson_id' => $lesson->getId()];
        } else {
            throw new \InvalidArgumentException('Either course or lesson must be provided');
        }

        $metadata['user_id'] = $user->getId();

        try {
            $session = Session:: create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $itemName,
                        ],
                        'unit_amount' => (int) ($amount * 100), // Montant en centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl .  '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'customer_email' => $user->getEmail(),
                'metadata' => $metadata,
            ]);

            return $session;
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Erreur lors de la création de la session Stripe: ' . $e->getMessage());
        }
    }

    public function handleSuccessfulPayment(string $sessionId): Purchase
    {
        try {
            $session = Session:: retrieve($sessionId);

            $metadata = $session->metadata;
            $userId = $metadata['user_id'];
            $type = $metadata['type'];

            $purchase = new Purchase();
            $purchase->setStripePaymentIntentId($session->payment_intent);
            $purchase->setAmount((string) ($session->amount_total / 100));
            $purchase->setStatus('completed');

            $this->purchaseRepository->save($purchase, true);

            return $purchase;
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Erreur lors de la récupération de la session Stripe: ' . $e->getMessage());
        }
    }
}
