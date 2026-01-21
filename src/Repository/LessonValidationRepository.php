<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LessonValidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonValidation:: class);
    }

    public function findByUserAndLesson(User $user, Lesson $lesson): ?LessonValidation
    {
        return $this->findOneBy(['user' => $user, 'lesson' => $lesson]);
    }

    public function save(LessonValidation $validation, bool $flush = false): void
    {
        $this->getEntityManager()->persist($validation);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}