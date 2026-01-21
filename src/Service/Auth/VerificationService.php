<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;

class VerificationService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function verifyUser(string $token): ?User
    {
        $user = $this->userRepository->findByVerificationToken($token);

        if (!$user) {
            return null;
        }

        if ($user->isVerified()) {
            return null;
        }

        $user->setVerified(true);
        $user->setVerificationToken(null);

        $this->userRepository->save($user, true);

        return $user;
    }
}