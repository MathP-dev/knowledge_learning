<?php

namespace App\Service\Payment;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use App\Repository\PurchaseRepository;

readonly class PurchaseService
{
    public function __construct(
        private PurchaseRepository $purchaseRepository
    ) {
    }

    public function createPurchase(User $user, string $paymentIntentId, string $amount, ?Course $course = null, ? Lesson $lesson = null): Purchase
    {
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setStripePaymentIntentId($paymentIntentId);
        $purchase->setAmount($amount);
        $purchase->setStatus('completed');

        if ($course) {
            $purchase->setCourse($course);
        }

        if ($lesson) {
            $purchase->setLesson($lesson);
        }

        $this->purchaseRepository->save($purchase, true);

        return $purchase;
    }

    public function getUserPurchases(User $user): array
    {
        return $this->purchaseRepository->findByUser($user);
    }

    public function hasUserPurchasedCourse(User $user, Course $course): bool
    {
        $purchases = $this->purchaseRepository->findByUser($user);

        foreach ($purchases as $purchase) {
            if ($purchase->getCourse() === $course) {
                return true;
            }
        }

        return false;
    }

    public function hasUserPurchasedLesson(User $user, Lesson $lesson): bool
    {
        $purchases = $this->purchaseRepository->findByUser($user);

        foreach ($purchases as $purchase) {
            if ($purchase->getLesson() === $lesson || $purchase->getCourse() === $lesson->getCourse()) {
                return true;
            }
        }

        return false;
    }

    public function getAllPurchases(): array
    {
        return $this->purchaseRepository->findAllPurchases();
    }
}
