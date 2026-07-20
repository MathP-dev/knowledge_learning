<?php

namespace App\Tests\Functional;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PurchaseFlowTest extends WebTestCase
{
    public function testUserCannotBuyWithoutBeingVerified(): void
    {
        $client = static::createClient();

        $user = $this->createUser('unverified' . uniqid() . '@example.com', false);
        $course = $this->createCourse();

        $client->loginUser($user);

        $token = $this->generateCsrfToken($client, 'cart_add_course' . $course->getId());

        $client->request('POST', '/cart/add/course/' . $course->getId(), [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('.alert-error, .alert-danger, .alert-warning');
    }

    public function testVerifiedUserCanAccessBuyPage(): void
    {
        $client = static::createClient();

        $user = $this->createUser('verified' . uniqid() . '@example.com', true);
        $course = $this->createCourse();

        $client->loginUser($user);

        $router = static::getContainer()->get('router');
        $courseUrl = $router->generate('app_course_show', ['slug' => $course->getSlug()]);

        // On charge la vraie page et on soumet le vrai formulaire qu'elle contient
        $crawler = $client->request('GET', $courseUrl);
        $form = $crawler->filter('form[action$="/cart/add/course/' . $course->getId() . '"]')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/cart');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'ajoutée au panier');

        // $crawler pointe maintenant sur /cart (résultat du followRedirect)
        // on récupère le formulaire de checkout qui s'y trouve, sans nouvelle requête GET
        // $="" => 'se termine par' car prix formaté
        $checkoutForm = $crawler->filter('form[action$="/cart/checkout"]')->form();
        $client->submit($checkoutForm);

        $this->assertResponseRedirects();
    }

    public function testGuestCannotAddToCart(): void
    {
        $client = static::createClient();

        $course = $this->createCourse();

        $client->request('POST', '/cart/add/course/' . $course->getId());

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

        $client->request('GET', '/lesson/' . $lesson->getSlug());

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

        $client->request('GET', '/lesson/' . $lesson->getSlug());

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

    private function generateCsrfToken(KernelBrowser $client, string $tokenId, string $warmupUrl = '/cart'): string
    {
        $client->request('GET', $warmupUrl);
        $request = $client->getRequest();

        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);

        $token = static::getContainer()
            ->get('security.csrf.token_manager')
            ->getToken($tokenId)
            ->getValue();

        $request->getSession()->save();
        $requestStack->pop();

        return $token;
    }
}
