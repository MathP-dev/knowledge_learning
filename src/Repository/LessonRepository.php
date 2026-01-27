<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, Lesson:: class);
    }

    public function findBySlug(string $slug): ?Lesson
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.course', 'c')
            ->addSelect('c')
            ->leftJoin('c.theme', 't')
            ->addSelect('t')
            ->where('l.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.course = :course')
            ->setParameter('course', $course)
            ->orderBy('l.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Lesson $lesson, bool $flush = false): void
    {
        $this->getEntityManager()->persist($lesson);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
