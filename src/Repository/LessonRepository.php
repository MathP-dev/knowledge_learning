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
    public function searchApiLessons(
        int $page = 1,
        int $itemsPerPage = 10,
        string $sort = 'position',
        string $direction = 'ASC',
        ?int $courseId = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $page = max(1, $page);
        $itemsPerPage = min(50, max(1, $itemsPerPage));

        // Whitelist anti-injection sur le champ de tri
        $allowedSorts = [
            'id' => 'l.id',
            'title' => 'l.title',
            'price' => 'l.price',
            'position' => 'l.position',
        ];
        $sortField = $allowedSorts[$sort] ?? $allowedSorts['position'];

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.course', 'c')
            ->addSelect('c');

        if ($courseId !== null) {
            $qb->andWhere('c.id = :courseId')
                ->setParameter('courseId', $courseId);
        }

        if ($minPrice !== null) {
            $qb->andWhere('l.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('l.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        return $qb
            ->orderBy($sortField, $direction)
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Afficher un total.
     */
    public function countApiLessons(
        ?int $courseId = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): int {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->leftJoin('l.course', 'c');

        if ($courseId !== null) {
            $qb->andWhere('c.id = :courseId')
                ->setParameter('courseId', $courseId);
        }

        if ($minPrice !== null) {
            $qb->andWhere('l.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('l.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
