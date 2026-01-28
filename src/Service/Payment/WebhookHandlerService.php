<?php

namespace App\Service\Payment;

use App\Entity\Purchase;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

readonly class WebhookHandlerService
{
    public function __construct(
        private string             $webhookSecret,
        private UserRepository     $userRepository,
        private CourseRepository   $courseRepository,
        private LessonRepository   $lessonRepository,
        private PurchaseRepository $purchaseRepository,
        private LoggerInterface    $logger
    ) {}

    public function constructEvent(string $payload, string $signature): Event
    {
        try {
            return Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Invalid webhook signature', 0, $e);
        }
    }

    public function handleEvent(Event $event): void
    {
        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutSessionCompleted($event);
        }
    }

    private function handleCheckoutSessionCompleted(Event $event): void
    {
        $session = $event->data->object;
        $metadata = $session->metadata;

        $userId = $metadata['user_id'] ?? null;
        $type = $metadata['type'] ?? null;

        if (!$userId || !$type) {
            $this->logger->error('Missing metadata in checkout session', [
                'session_id' => $session->id
            ]);
            return;
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->logger->error('User not found', ['user_id' => $userId]);
            return;
        }

        if ($type === 'course') {
            $courseId = $metadata['course_id'] ?? null;
            $course = $courseId ? $this->courseRepository->find($courseId) : null;

            if (!$course) {
                $this->logger->error('Course not found', ['course_id' => $courseId]);
                return;
            }

            $this->createPurchase($user, $session, $course, null);

        } elseif ($type === 'lesson') {
            $lessonId = $metadata['lesson_id'] ?? null;
            $lesson = $lessonId ? $this->lessonRepository->find($lessonId) : null;

            if (!$lesson) {
                $this->logger->error('Lesson not found', ['lesson_id' => $lessonId]);
                return;
            }

            $this->createPurchase($user, $session, null, $lesson);
        }
    }

    private function createPurchase($user, $session, $course = null, $lesson = null): void
    {
        $existingPurchase = $this->purchaseRepository->findOneBy([
            'stripePaymentIntentId' => $session->payment_intent
        ]);

        if ($existingPurchase) {
            return;
        }

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setStripePaymentIntentId($session->payment_intent);
        $purchase->setAmount((string) ($session->amount_total / 100));
        $purchase->setStatus('completed');
        $purchase->setPurchasedAt(new \DateTimeImmutable());

        if ($course) {
            $purchase->setCourse($course);
        }

        if ($lesson) {
            $purchase->setLesson($lesson);
        }

        $this->purchaseRepository->save($purchase, true);

        $this->logger->info('Purchase completed', [
            'user_id' => $user->getId(),
            'type' => $course ? 'course' : 'lesson',
            'item_id' => $course?->getId() ?? $lesson?->getId()
        ]);
    }
}
