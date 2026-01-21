<?php

namespace App\Repository;

use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.purchasedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStripePaymentIntentId(string $paymentIntentId): ?Purchase
    {
        return $this->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);
    }

    public function save(Purchase $purchase, bool $flush = false): void
    {
        $this->getEntityManager()->persist($purchase);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllPurchases(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->leftJoin('p.course', 'c')
            ->addSelect('c')
            ->leftJoin('p.lesson', 'l')
            ->addSelect('l')
            ->orderBy('p.purchasedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}