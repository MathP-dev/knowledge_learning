<?php

namespace App\Tests\Functional;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PurchaseFlowTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self:: bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testUserCannotBuyWithoutBeingVerified(): void
    {
        $client = static::createClient();

        // Créer un utilisateur non vérifié
        $user = $this->createUser('unverified@example.com', false);
        $course = $this->createCourse();

        $client->loginUser($user);

        $client->request('GET', '/cursus/' . $course->getSlug() . '/acheter');

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('.alert-error, .alert-danger');
    }

    public function testVerifiedUserCanAccessBuyPage(): void
    {
        $client = static::createClient();

        $user = $this->createUser('verified@example.com', true);
        $course = $this->createCourse();

        $client->loginUser($user);

        // Note: Dans un vrai test, Stripe redirigerait vers une page externe
        // Ici on vérifie juste qu'on peut accéder à la page d'achat
        $client->request('GET', '/cursus/' . $course->getSlug() . '/acheter');

        // Stripe redirige vers une URL externe, donc on ne peut pas tester la réponse complète
        // On vérifie juste qu'il n'y a pas d'erreur de serveur
        $this->assertNotEquals(500, $client->getResponse()->getStatusCode());
    }

    public function testGuestCannotBuyCourse(): void
    {
        $client = static::createClient();

        $course = $this->createCourse();

        $client->request('GET', '/cursus/' . $course->getSlug() . '/acheter');

        $this->assertResponseRedirects('/connexion');
    }

    public function testUserCanViewPurchasedContent(): void
    {
        $client = static::createClient();

        $user = $this->createUser('buyer@example.com', true);
        $course = $this->createCourse();
        $lesson = $this->createLesson($course);

        // Simuler un achat
        $this->createPurchase($user, $course);

        $client->loginUser($user);

        $client->request('GET', '/lecon/' . $lesson->getSlug());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('. alert-success', 'Vous avez accès à cette leçon');
    }

    public function testUserCannotViewUnpurchasedContent(): void
    {
        $client = static::createClient();

        $user = $this->createUser('nonbuyer@example.com', true);
        $course = $this->createCourse();
        $lesson = $this->createLesson($course);

        $client->loginUser($user);

        $client->request('GET', '/lecon/' . $lesson->getSlug());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('. alert-warning', 'Vous n\'avez pas accès');
    }

    private function createUser(string $email, bool $verified): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, 'Password123!'));
        $user->setVerified($verified);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createCourse(): Course
    {
        $theme = new Theme();
        $theme->setName('Test Theme');
        $theme->setSlug('test-theme');
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

    private function createLesson(Course $course): Lesson
    {
        $lesson = new Lesson();
        $lesson->setTitle('Test Lesson');
        $lesson->setSlug('test-lesson-' . uniqid());
        $lesson->setPrice('26.00');
        $lesson->setPosition(1);
        $lesson->setCourse($course);

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        return $lesson;
    }

    private function createPurchase(User $user, Course $course): void
    {
        $purchase = new \App\Entity\Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setAmount($course->getPrice());
        $purchase->setStripePaymentIntentId('pi_test_' . uniqid());
        $purchase->setStatus('completed');

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
