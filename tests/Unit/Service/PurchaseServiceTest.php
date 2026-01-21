<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use App\Service\Payment\PurchaseService;
use PHPUnit\Framework\TestCase;

class PurchaseServiceTest extends TestCase
{
    private PurchaseService $purchaseService;
    private PurchaseRepository $purchaseRepository;

    protected function setUp(): void
    {
        $this->purchaseRepository = $this->createMock(PurchaseRepository::class);
        $this->purchaseService = new PurchaseService($this->purchaseRepository);
    }

    public function testCreatePurchaseForCourse(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();

        $this->purchaseRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (Purchase $purchase) use ($user, $course) {
                    return $purchase->getUser() === $user
                        && $purchase->getCourse() === $course
                        && $purchase->getLesson() === null
                        && $purchase->getAmount() === '50.00'
                        && $purchase->getStatus() === 'completed';
                }),
                true
            );

        // Act
        $purchase = $this->purchaseService->createPurchase(
            $user,
            'pi_test_123',
            '50.00',
            $course,
            null
        );

        // Assert
        $this->assertInstanceOf(Purchase::class, $purchase);
        $this->assertEquals($user, $purchase->getUser());
        $this->assertEquals($course, $purchase->getCourse());
        $this->assertNull($purchase->getLesson());
        $this->assertEquals('50.00', $purchase->getAmount());
        $this->assertEquals('completed', $purchase->getStatus());
    }

    public function testCreatePurchaseForLesson(): void
    {
        // Arrange
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->purchaseRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Purchase::class), true);

        // Act
        $purchase = $this->purchaseService->createPurchase(
            $user,
            'pi_test_456',
            '26.00',
            null,
            $lesson
        );

        // Assert
        $this->assertInstanceOf(Purchase::class, $purchase);
        $this->assertEquals($user, $purchase->getUser());
        $this->assertNull($purchase->getCourse());
        $this->assertEquals($lesson, $purchase->getLesson());
        $this->assertEquals('26.00', $purchase->getAmount());
    }

    public function testHasUserPurchasedCourse(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setAmount('50.00');
        $purchase->setStripePaymentIntentId('pi_test');
        $purchase->setStatus('completed');

        $this->purchaseRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([$purchase]);

        // Act
        $result = $this->purchaseService->hasUserPurchasedCourse($user, $course);

        // Assert
        $this->assertTrue($result);
    }

    public function testHasUserNotPurchasedCourse(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();

        $this->purchaseRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([]);

        // Act
        $result = $this->purchaseService->hasUserPurchasedCourse($user, $course);

        // Assert
        $this->assertFalse($result);
    }

    public function testHasUserPurchasedLesson(): void
    {
        // Arrange
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setLesson($lesson);
        $purchase->setAmount('26.00');
        $purchase->setStripePaymentIntentId('pi_test');
        $purchase->setStatus('completed');

        $this->purchaseRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([$purchase]);

        // Act
        $result = $this->purchaseService->hasUserPurchasedLesson($user, $lesson);

        // Assert
        $this->assertTrue($result);
    }

    public function testHasUserPurchasedLessonViaCourse(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();
        $lesson = $this->createLesson();
        $lesson->setCourse($course);

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setAmount('50.00');
        $purchase->setStripePaymentIntentId('pi_test');
        $purchase->setStatus('completed');

        $this->purchaseRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn([$purchase]);

        // Act
        $result = $this->purchaseService->hasUserPurchasedLesson($user, $lesson);

        // Assert
        $this->assertTrue($result);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');
        $user->setVerified(true);
        return $user;
    }

    private function createCourse(): Course
    {
        $theme = new Theme();
        $theme->setName('Test Theme');
        $theme->setSlug('test-theme');

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setSlug('test-course');
        $course->setPrice('50.00');
        $course->setTheme($theme);
        return $course;
    }

    private function createLesson(): Lesson
    {
        $course = $this->createCourse();

        $lesson = new Lesson();
        $lesson->setTitle('Test Lesson');
        $lesson->setSlug('test-lesson');
        $lesson->setPrice('26.00');
        $lesson->setPosition(1);
        $lesson->setCourse($course);
        return $lesson;
    }
}
