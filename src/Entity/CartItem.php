<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    #[ORM\Column]
    private int $price;

    #[ORM\Column(type: 'string', enumType: CartItemType::class)]
    private CartItemType $type;

    #[ORM\Column(nullable: true)]
    private ?int $lessonId = null;

    #[ORM\Column(nullable: true)]
    private ?int $courseId = null;


    public function getId(): ?int { return $this->id; }

    public function getCart(): Cart { return $this->cart; }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;
        return $this;
    }

    public function getPrice(): int { return $this->price; }

    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getType(): CartItemType { return $this->type; }

    public function setType(CartItemType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getLessonId(): ?int { return $this->lessonId; }

    public function setLessonId(?int $lessonId): static
    {
        $this->lessonId = $lessonId;
        return $this;
    }

    public function getCourseId(): ?int { return $this->courseId; }

    public function setCourseId(?int $courseId): static
    {
        $this->courseId = $courseId;
        return $this;
    }
}

