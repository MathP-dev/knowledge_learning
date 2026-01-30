<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/connexion');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Connexion');
        $this->assertCount(1, $crawler->filter('input[name="email"]'));
        $this->assertCount(1, $crawler->filter('input[name="password"]'));
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        // Créer un utilisateur de test avec email unique
        $email = 'test.login' . uniqid() . '@example.com';
        $user = $this->createVerifiedUser($email, 'Password123!');

        $crawler = $client->request('GET', '/connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            'email' => $email,
            'password' => 'Password123!',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidPassword(): void
    {
        $client = static::createClient();

        // Créer un utilisateur de test avec email unique
        $email = 'test.invalid' . uniqid() . '@example.com';
        $this->createVerifiedUser($email, 'Password123!');

        $crawler = $client->request('GET', '/connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            'email' => $email,
            'password' => 'WrongPassword',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/connexion');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testLoginWithNonExistentUser(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/connexion');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        $email = 'test.logout' . uniqid() . '@example.com';
        $user = $this->createVerifiedUser($email, 'Password123!');

        // Se connecter
        $client->loginUser($user);

        // Vérifier qu'on est connecté
        $client->request('GET', '/mon-compte');
        $this->assertResponseIsSuccessful();

        // Se déconnecter
        $client->request('GET', '/deconnexion');
        $this->assertResponseRedirects();

        // Vérifier qu'on ne peut plus accéder aux pages protégées
        $client->request('GET', '/mon-compte');
        $this->assertResponseRedirects('/connexion');
    }

    private function createVerifiedUser(string $email, string $password): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setVerified(true);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
