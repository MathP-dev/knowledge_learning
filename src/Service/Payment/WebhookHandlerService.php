<?php

namespace App\Service\Payment;

use App\Entity\CartItem;
use App\Entity\CartItemType;
use App\Entity\Purchase;
use App\Repository\CartRepository;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;

readonly class WebhookHandlerService
{
    public function __construct(
        private string $webhookSecret,
        private UserRepository $userRepository,
        private CourseRepository $courseRepository,
        private LessonRepository $lessonRepository,
        private PurchaseRepository $purchaseRepository,
        private CartRepository $cartRepository,
        private LoggerInterface $logger,
        private EntityManagerInterface $em
    ) {}

    public function constructEvent(string $payload, string $signature): Event
    {
        try {
            return Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Invalid webhook signature', 0, $e);
        }
    }

    public function handleEvent(Event $event): void
    {
        if ($event->type !== 'payment_intent.succeeded') {
            return;
        }

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;
        $metadata = $paymentIntent->metadata;

        if (!isset($metadata->user_id, $metadata->cart_id)) {
            $this->logger->error('Invalid metadata in payment intent', [
                'metadata' => (array) $metadata,
                'payment_intent_id' => $paymentIntent->id,
            ]);
            return;
        }

        if ($this->purchaseRepository->findByStripePaymentIntentId((string) $paymentIntent->id)) {
            $this->logger->info('Payment intent already processed', [
                'payment_intent_id' => $paymentIntent->id,
            ]);
            return;
        }

        $this->handleCartPurchase($paymentIntent, $metadata);
    }

    private function handleCartPurchase(PaymentIntent $paymentIntent, object $metadata): void
    {
        $userId = (int) $metadata->user_id;
        $cartId = (int) $metadata->cart_id;
        $paymentIntentId = (string) $paymentIntent->id;
        $amountReceived = (int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0);

        $user = $this->userRepository->find($userId);
        $cart = $this->cartRepository->find($cartId);

        if (!$user) {
            $this->logger->error('User not found in cart purchase', ['user_id' => $userId]);
            return;
        }

        if (!$cart) {
            $this->logger->error('Cart not found in cart purchase', ['cart_id' => $cartId]);
            return;
        }

        if ($cart->getUser()->getId() !== $user->getId()) {
            $this->logger->error('Cart/user mismatch in cart purchase', [
                'user_id' => $user->getId(),
                'cart_id' => $cart->getId(),
            ]);
            return;
        }

        if ($amountReceived <= 0) {
            $this->logger->error('Invalid payment amount received', [
                'payment_intent_id' => $paymentIntentId,
                'cart_id' => $cart->getId(),
            ]);
            return;
        }

        $items = array_values($cart->getItems()->toArray());
        if ($items === []) {
            $this->logger->info('Cart already processed', [
                'user_id' => $user->getId(),
                'cart_id' => $cart->getId(),
                'payment_intent' => $paymentIntentId,
            ]);
            return;
        }

        $expectedTotal = array_reduce(
            $items,
            static fn (int $total, CartItem $item): int => $total + $item->getPrice(),
            0
        );

        if ($expectedTotal !== $amountReceived) {
            $this->logger->error('Cart total mismatch', [
                'cart_id' => $cart->getId(),
                'expected_total' => $expectedTotal,
                'stripe_total' => $amountReceived,
            ]);
            return;
        }

        $this->em->beginTransaction();

        try {
            $this->em->lock($cart, LockMode::PESSIMISTIC_WRITE);

            $items = array_values($cart->getItems()->toArray());
            if ($items === []) {
                $this->em->commit();
                return;
            }

            $currentTotal = array_reduce(
                $items,
                static fn (int $total, CartItem $item): int => $total + $item->getPrice(),
                0
            );

            if ($currentTotal !== $amountReceived) {
                throw new \RuntimeException('Cart total changed during checkout');
            }

            foreach ($items as $item) {
                $purchase = new Purchase();
                $purchase->setUser($user);
                $purchase->setStripePaymentIntentId($paymentIntentId);
                $purchase->setAmount(number_format($item->getPrice() / 100, 2, '.', ''));
                $purchase->setStatus('completed');
                $purchase->setPurchasedAt(new \DateTimeImmutable());

                if ($item->getType() === CartItemType::LESSON) {
                    $lesson = $this->lessonRepository->find($item->getLessonId());
                    if (!$lesson) {
                        throw new \RuntimeException('Lesson not found for cart item ' . $item->getId());
                    }
                    $purchase->setLesson($lesson);
                } else {
                    $course = $this->courseRepository->find($item->getCourseId());
                    if (!$course) {
                        throw new \RuntimeException('Course not found for cart item ' . $item->getId());
                    }
                    $purchase->setCourse($course);
                }

                $this->purchaseRepository->save($purchase, false);
            }

            foreach ($items as $item) {
                $this->em->remove($item);
            }

            $cart->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->em->commit();

            $this->logger->info('Cart purchase completed successfully', [
                'user_id' => $user->getId(),
                'cart_id' => $cart->getId(),
                'items_count' => count($items),
                'total_amount' => $amountReceived / 100,
            ]);
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }

            throw $e;
        }
    }
}
