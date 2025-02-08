<?php

namespace App\Entity;

use App\Repository\SocialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialRepository::class)]
class Social
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'socials')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $social = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialUsername = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $socialPassword = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

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

    public function getSocial(): ?string
    {
        return $this->social;
    }

    public function setSocial(?string $social): static
    {
        $this->social = $social;

        return $this;
    }

    public function getSocialUsername(): ?string
    {
        return $this->socialUsername;
    }

    public function setSocialUsername(?string $socialUsername): static
    {
        $this->socialUsername = $socialUsername;

        return $this;
    }

    public function getSocialEmail(): ?string
    {
        return $this->socialEmail;
    }

    public function setSocialEmail(?string $socialEmail): static
    {
        $this->socialEmail = $socialEmail;

        return $this;
    }

    public function getSocialPassword(): ?string
    {
        return $this->socialPassword;
    }

    public function setSocialPassword(?string $socialPassword): static
    {
        $this->socialPassword = $socialPassword;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
