<?php

namespace App\Service\Payment;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\CartItemType;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PurchaseRepository $purchaseRepo,
        private PurchaseService $purchaseService,
    ) {}

    /**
     * Récupère ou crée un panier pour l'utilisateur
     */
    public function getOrCreateCart(User $user): Cart
    {
        $cart = $user->getCart();
        if ($cart === null) {
            $cart = new Cart($user);
            $user->setCart($cart);
            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    /**
     * Ajoute une leçon au panier
     */
    public function addLesson(Cart $cart, Lesson $lesson): void
    {
        $this->addToCart($cart, $lesson, CartItemType::LESSON);
    }

    /**
     * Ajoute une formation au panier
     */
    public function addCourse(Cart $cart, Course $course): void
    {
        $this->addToCart($cart, $course, CartItemType::COURSE);
    }

    /**
     * Logique commune pour ajouter un item au panier
     */
    private function addToCart(Cart $cart, Lesson|Course $item, CartItemType $type): void
    {
        $user = $cart->getUser();
        $itemId = $item->getId();
        $price = $item->getPrice();

        if ($itemId === null) {
            throw new \LogicException('L\'élément sélectionné est invalide.');
        }

        if ($price === null) {
            throw new \LogicException('L\'élément sélectionné n\'a pas de prix.');
        }

        if ($item instanceof Lesson && $this->purchaseService->hasUserPurchasedLesson($user, $item)) {
            throw new \LogicException('Vous avez déjà accès à cette leçon.');
        }

        if ($item instanceof Course && $this->purchaseService->hasUserPurchasedCourse($user, $item)) {
            throw new \LogicException('Vous avez déjà acheté cette formation.');
        }

        if ($cart->hasItem($type, $itemId)) {
            throw new \LogicException('Cet élément est déjà dans votre panier.');
        }

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setPrice((int) round(((float) $price) * 100));
        $cartItem->setType($type);

        if ($item instanceof Lesson) {
            $cartItem->setLessonId($itemId);
        } else {
            $cartItem->setCourseId($itemId);
        }

        $this->em->persist($cartItem);
        $cart->addItem($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /**
     * Retire un item du panier
     */
    public function removeItem(Cart $cart, CartItem $item): void
    {
        $cart->removeItem($item);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->remove($item);
        $this->em->flush();
    }

    /**
     * Vide le panier
     */
    public function clearCart(Cart $cart): void
    {
        $cart->clear();
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /**
     * Récupère le montant total du panier en euros
     */
    public function getTotalAmount(Cart $cart): float
    {
        return $cart->getTotalAmount() / 100;
    }
}
