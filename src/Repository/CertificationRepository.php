<?php

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certification::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->leftJoin('c.course', 'co')
            ->addSelect('co')
            ->orderBy('c.obtainedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndCourse(User $user, Course $course): ?Certification
    {
        return $this->findOneBy(['user' => $user, 'course' => $course]);
    }

    public function save(Certification $certification, bool $flush = false): void
    {
        $this->getEntityManager()->persist($certification);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
