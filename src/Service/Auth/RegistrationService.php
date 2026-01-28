<?php

namespace App\Service\Auth;

use App\DTO\RegistrationDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Email\EmailService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class RegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailService $emailService
    ) {
    }

    public function register(RegistrationDTO $dto): User
    {
        $user = new User();
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        $verificationToken = Uuid::v4()->toRfc4122();
        $user->setVerificationToken($verificationToken);

        $this->userRepository->save($user, true);

        $this->emailService->sendVerificationEmail($user);

        return $user;
    }
}
