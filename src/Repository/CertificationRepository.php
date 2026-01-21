<?php

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\Theme;
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
            ->leftJoin('c.theme', 't')
            ->addSelect('t')
            ->orderBy('c.obtainedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndTheme(User $user, Theme $theme): ?Certification
    {
        return $this->findOneBy(['user' => $user, 'theme' => $theme]);
    }

    public function save(Certification $certification, bool $flush = false): void
    {
        $this->getEntityManager()->persist($certification);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}