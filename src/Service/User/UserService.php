<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAllUsers();
    }

    public function promoteToAdmin(User $user): void
    {
        $roles = $user->getRoles();
        if (! in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $this->userRepository->save($user, true);
        }
    }

    public function demoteFromAdmin(User $user): void
    {
        $roles = $user->getRoles();
        $roles = array_filter($roles, fn($role) => $role !== 'ROLE_ADMIN');
        $user->setRoles(array_values($roles));
        $this->userRepository->save($user, true);
    }
}