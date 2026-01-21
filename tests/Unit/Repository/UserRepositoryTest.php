<?php

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->userRepository = $this->entityManager->getRepository(User:: class);
    }

    public function testFindByVerificationToken(): void
    {
        // Arrange
        $token = 'test-token-' . uniqid();
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test' . uniqid() . '@example.com');
        $user->setPassword('hashed');
        $user->setVerificationToken($token);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Act
        $foundUser = $this->userRepository->findByVerificationToken($token);

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($token, $foundUser->getVerificationToken());
        $this->assertEquals($user->getId(), $foundUser->getId());
    }

    public function testFindByVerificationTokenReturnsNullForInvalidToken(): void
    {
        // Act
        $foundUser = $this->userRepository->findByVerificationToken('nonexistent-token');

        // Assert
        $this->assertNull($foundUser);
    }

    public function testSaveUser(): void
    {
        // Arrange
        $user = new User();
        $user->setFirstName('Save');
        $user->setLastName('Test');
        $user->setEmail('save' . uniqid() . '@example.com');
        $user->setPassword('hashed');

        // Act
        $this->userRepository->save($user, true);

        // Assert
        $this->assertNotNull($user->getId());
        $foundUser = $this->userRepository->find($user->getId());
        $this->assertEquals($user->getEmail(), $foundUser->getEmail());
    }

    public function testFindAllUsers(): void
    {
        // Arrange
        $user1 = new User();
        $user1->setFirstName('User1');
        $user1->setLastName('Test');
        $user1->setEmail('user1' . uniqid() . '@example.com');
        $user1->setPassword('hashed');

        $user2 = new User();
        $user2->setFirstName('User2');
        $user2->setLastName('Test');
        $user2->setEmail('user2' . uniqid() . '@example.com');
        $user2->setPassword('hashed');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        // Act
        $users = $this->userRepository->findAllUsers();

        // Assert
        $this->assertIsArray($users);
        $this->assertGreaterThanOrEqual(2, count($users));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}