<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\VerificationService;
use PHPUnit\Framework\TestCase;

class VerificationServiceTest extends TestCase
{
    private VerificationService $verificationService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->verificationService = new VerificationService($this->userRepository);
    }

    public function testVerifyUserWithValidToken(): void
    {
        // Arrange
        $token = 'valid-token-123';
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@example. com');
        $user->setPassword('hashed');
        $user->setVerificationToken($token);
        $user->setVerified(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findByVerificationToken')
            ->with($token)
            ->willReturn($user);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (User $savedUser) {
                    return $savedUser->isVerified() === true
                        && $savedUser->getVerificationToken() === null;
                }),
                true
            );

        // Act
        $result = $this->verificationService->verifyUser($token);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($result->isVerified());
        $this->assertNull($result->getVerificationToken());
    }

    public function testVerifyUserWithInvalidToken(): void
    {
        // Arrange
        $token = 'invalid-token';

        $this->userRepository
            ->expects($this->once())
            ->method('findByVerificationToken')
            ->with($token)
            ->willReturn(null);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        // Act
        $result = $this->verificationService->verifyUser($token);

        // Assert
        $this->assertNull($result);
    }

    public function testVerifyUserAlreadyVerified(): void
    {
        // Arrange
        $token = 'already-used-token';
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');
        $user->setVerificationToken($token);
        $user->setVerified(true); // Déjà vérifié

        $this->userRepository
            ->expects($this->once())
            ->method('findByVerificationToken')
            ->with($token)
            ->willReturn($user);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        // Act
        $result = $this->verificationService->verifyUser($token);

        // Assert
        $this->assertNull($result);
    }
}
