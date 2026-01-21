<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    public function findBySlug(string $slug): ?Theme
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findAllThemes(): array
    {
        return $this->findAll();
    }

    public function save(Theme $theme, bool $flush = false): void
    {
        $this->getEntityManager()->persist($theme);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}