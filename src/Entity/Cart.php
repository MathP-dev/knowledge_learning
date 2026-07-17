<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'cart', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): User { return $this->user; }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getItems(): Collection { return $this->items; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function addItem(CartItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCart($this);
        }
        return $this;
    }

    public function removeItem(CartItem $item): static
    {
        $this->items->removeElement($item);
        return $this;
    }

    public function getTotalAmount(): int
    {
        return array_reduce(
            $this->items->toArray(),
            fn(int $total, CartItem $item) => $total + $item->getPrice(),
            0
        );
    }

    public function clear(): static
    {
        $this->items->clear();
        return $this;
    }

    public function hasItem(CartItemType $type, int $id): bool
    {
        foreach ($this->items as $item) {
            if ($type === CartItemType::LESSON && $item->getLessonId() === $id) {
                return true;
            }
            if ($type === CartItemType::COURSE && $item->getCourseId() === $id) {
                return true;
            }
        }
        return false;
    }
}

