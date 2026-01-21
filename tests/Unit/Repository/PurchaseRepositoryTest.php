<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Course;
use App\Entity\Purchase;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PurchaseRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private PurchaseRepository $purchaseRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->purchaseRepository = $this->entityManager->getRepository(Purchase::class);
    }

    public function testFindByUser(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();

        $purchase1 = new Purchase();
        $purchase1->setUser($user);
        $purchase1->setCourse($course);
        $purchase1->setAmount('50.00');
        $purchase1->setStripePaymentIntentId('pi_test_1_' . uniqid());
        $purchase1->setStatus('completed');

        $purchase2 = new Purchase();
        $purchase2->setUser($user);
        $purchase2->setCourse($course);
        $purchase2->setAmount('60.00');
        $purchase2->setStripePaymentIntentId('pi_test_2_' . uniqid());
        $purchase2->setStatus('completed');

        $this->entityManager->persist($purchase1);
        $this->entityManager->persist($purchase2);
        $this->entityManager->flush();

        // Act
        $purchases = $this->purchaseRepository->findByUser($user);

        // Assert
        $this->assertIsArray($purchases);
        $this->assertGreaterThanOrEqual(2, count($purchases));
    }

    public function testFindByStripePaymentIntentId(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();
        $paymentIntentId = 'pi_test_unique_' . uniqid();

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setAmount('50.00');
        $purchase->setStripePaymentIntentId($paymentIntentId);
        $purchase->setStatus('completed');

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        // Act
        $foundPurchase = $this->purchaseRepository->findByStripePaymentIntentId($paymentIntentId);

        // Assert
        $this->assertInstanceOf(Purchase::class, $foundPurchase);
        $this->assertEquals($paymentIntentId, $foundPurchase->getStripePaymentIntentId());
    }

    public function testSavePurchase(): void
    {
        // Arrange
        $user = $this->createUser();
        $course = $this->createCourse();

        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setAmount('50.00');
        $purchase->setStripePaymentIntentId('pi_test_save_' . uniqid());
        $purchase->setStatus('completed');

        // Act
        $this->purchaseRepository->save($purchase, true);

        // Assert
        $this->assertNotNull($purchase->getId());
        $foundPurchase = $this->purchaseRepository->find($purchase->getId());
        $this->assertEquals($purchase->getAmount(), $foundPurchase->getAmount());
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test' . uniqid() . '@example.com');
        $user->setPassword('hashed');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createCourse(): Course
    {
        $theme = new Theme();
        $theme->setName('Test Theme ' . uniqid());
        $theme->setSlug('test-theme-' . uniqid());
        $this->entityManager->persist($theme);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setSlug('test-course-' . uniqid());
        $course->setPrice('50.00');
        $course->setTheme($theme);

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $course;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}