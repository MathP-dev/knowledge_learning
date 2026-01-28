<?php
// src/Entity/Certification.php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
#[ORM\UniqueConstraint(name: 'user_course_unique', columns: ['user_id', 'course_id'])] // ← Changé theme en course
class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $certificateNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $obtainedAt = null;

    public function __construct()
    {
        $this->obtainedAt = new \DateTimeImmutable();
        $this->certificateNumber = $this->generateCertificateNumber();
    }

    private function generateCertificateNumber(): string
    {
        return 'KL-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getCertificateNumber(): ?string
    {
        return $this->certificateNumber;
    }

    public function setCertificateNumber(string $certificateNumber): static
    {
        $this->certificateNumber = $certificateNumber;
        return $this;
    }

    public function getObtainedAt(): ?\DateTimeImmutable
    {
        return $this->obtainedAt;
    }

    public function setObtainedAt(\DateTimeImmutable $obtainedAt): static
    {
        $this->obtainedAt = $obtainedAt;
        return $this;
    }
}
