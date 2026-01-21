<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function findBySlug(string $slug): ?Course
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.theme', 't')
            ->addSelect('t')
            ->leftJoin('c.lessons', 'l')
            ->addSelect('l')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByTheme(Theme $theme): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllCourses(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.theme', 't')
            ->addSelect('t')
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Course $course, bool $flush = false): void
    {
        $this->getEntityManager()->persist($course);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}