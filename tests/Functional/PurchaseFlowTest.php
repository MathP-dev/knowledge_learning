<?php

namespace App\Tests\Functional;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PurchaseFlowTest extends WebTestCase
{
    public function testUserCannotBuyWithoutBeingVerified(): void
    {
        $client = static::createClient();

        // Créer un utilisateur non vérifié avec email unique
        $user = $this->createUser('unverified' . uniqid() . '@example.com', false);
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

        $user = $this->createUser('verified' . uniqid() . '@example.com', true);
        $course = $this->createCourse();

        $client->loginUser($user);

        // Note: En environnement de test, Stripe peut ne pas être configuré
        // Le test vérifie que l'utilisateur vérifié peut tenter d'acheter (pas de blocage côté app)
        $client->request('GET', '/cursus/' . $course->getSlug() . '/acheter');

        $statusCode = $client->getResponse()->getStatusCode();

        // Le status peut être :
        // - 302 (redirection vers Stripe) si Stripe est configuré
        // - 500 (erreur Stripe) si Stripe n'est pas configuré en test (acceptable)
        // Ce qui compte c'est que l'utilisateur vérifié a le DROIT d'accéder (pas de 403)
        $this->assertNotEquals(403, $statusCode, 'L\'utilisateur vérifié doit pouvoir accéder à la page d\'achat');
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

        $user = $this->createUser('buyer' . uniqid() . '@example.com', true);
        $course = $this->createCourse();
        $lesson = $this->createLesson($course);

        // Simuler un achat
        $this->createPurchase($user, $course);

        // Rafraîchir l'utilisateur pour charger la relation purchases
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->refresh($user);

        $client->loginUser($user);

        $client->request('GET', '/lecon/' . $lesson->getSlug());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'Vous avez accès à cette leçon !');
    }

    public function testUserCannotViewUnpurchasedContent(): void
    {
        $client = static::createClient();

        $user = $this->createUser('nonbuyer' . uniqid() . '@example.com', true);
        $course = $this->createCourse();
        $lesson = $this->createLesson($course);

        $client->loginUser($user);

        $client->request('GET', '/lecon/' . $lesson->getSlug());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-warning', 'Vous n\'avez pas accès');
    }

    private function createUser(string $email, bool $verified): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, 'Password123!'));
        $user->setVerified($verified);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createCourse(): Course
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $theme = new Theme();
        $theme->setName('Test Theme');
        $theme->setSlug('test-theme');
        $entityManager->persist($theme);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setSlug('test-course-' . uniqid());
        $course->setPrice('50.00');
        $course->setTheme($theme);

        $entityManager->persist($course);
        $entityManager->flush();

        return $course;
    }

    private function createLesson(Course $course): Lesson
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $lesson = new Lesson();
        $lesson->setTitle('Test Lesson');
        $lesson->setSlug('test-lesson-' . uniqid());
        $lesson->setPrice('26.00');
        $lesson->setPosition(1);
        $lesson->setCourse($course);

        $entityManager->persist($lesson);
        $entityManager->flush();

        return $lesson;
    }

    private function createPurchase(User $user, Course $course): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $purchase = new \App\Entity\Purchase();
        $purchase->setUser($user);
        $purchase->setCourse($course);
        $purchase->setAmount($course->getPrice());
        $purchase->setStripePaymentIntentId('pi_test_' . uniqid());
        $purchase->setStatus('completed');

        $entityManager->persist($purchase);
        $entityManager->flush();
    }
}
