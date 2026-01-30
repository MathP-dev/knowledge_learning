<?php

namespace App\Tests\Unit\Service;

use App\DTO\RegistrationDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\RegistrationService;
use App\Service\Email\EmailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationServiceTest extends TestCase
{
    private RegistrationService $registrationService;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private EmailService $emailService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->registrationService = new RegistrationService(
            $this->userRepository,
            $this->passwordHasher,
            $this->emailService
        );
    }

    public function testRegisterCreatesUserWithCorrectData(): void
    {
        // Arrange
        $dto = new RegistrationDTO();
        $dto->firstName = 'John';
        $dto->lastName = 'Doe';
        $dto->email = 'john.doe@example.com';
        $dto->password = 'Password123!';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (User $user) {
                    return $user->getFirstName() === 'John'
                        && $user->getLastName() === 'Doe'
                        && $user->getEmail() === 'john.doe@example.com'
                        && $user->getPassword() === 'hashed_password'
                        && ! $user->isVerified()
                        && $user->getVerificationToken() !== null;
                }),
                true
            );

        $this->emailService
            ->expects($this->once())
            ->method('sendVerificationEmail');

        // Act
        $user = $this->registrationService->register($dto);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('john.doe@example.com', $user->getEmail());
        $this->assertFalse($user->isVerified());
        $this->assertNotNull($user->getVerificationToken());
    }

    public function testRegisterSendsVerificationEmail(): void
    {
        // Arrange
        $dto = new RegistrationDTO();
        $dto->firstName = 'Jane';
        $dto->lastName = 'Smith';
        $dto->email = 'jane.smith@example.com';
        $dto->password = 'Password123!';

        $this->passwordHasher
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->emailService
            ->expects($this->once())
            ->method('sendVerificationEmail')
            ->with($this->isInstanceOf(User::class));

        // Act
        $this->registrationService->register($dto);
    }
}
