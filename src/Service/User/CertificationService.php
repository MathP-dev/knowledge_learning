<?php

namespace App\Service\User;

use App\Entity\Certification;
use App\Entity\Course;
use App\Entity\User;
use App\Repository\CertificationRepository;

readonly class CertificationService
{
    public function __construct(
        private CertificationRepository $certificationRepository
    ) {
    }

    public function awardCertification(User $user, Course $course): void
    {
        $existingCertification = $this->certificationRepository->findByUserAndCourse($user, $course);

        if ($existingCertification) {
            return;
        }

        $certification = new Certification();
        $certification->setUser($user);
        $certification->setCourse($course);

        $this->certificationRepository->save($certification, true);
    }

    public function getUserCertifications(User $user): array
    {
        return $this->certificationRepository->findByUser($user);
    }
}
