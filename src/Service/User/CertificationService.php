<?php

namespace App\Service\User;

use App\Entity\Certification;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\CertificationRepository;

class CertificationService
{
    public function __construct(
        private CertificationRepository $certificationRepository
    ) {
    }

    public function awardCertification(User $user, Theme $theme): void
    {
        $existingCertification = $this->certificationRepository->findByUserAndTheme($user, $theme);

        if ($existingCertification) {
            return;
        }

        $certification = new Certification();
        $certification->setUser($user);
        $certification->setTheme($theme);

        $this->certificationRepository->save($certification, true);
    }

    public function getUserCertifications(User $user): array
    {
        return $this->certificationRepository->findByUser($user);
    }
}