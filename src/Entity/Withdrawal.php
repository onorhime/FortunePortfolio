<?php

namespace App\Entity;

use App\Repository\WithdrawalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WithdrawalRepository::class)]
class Withdrawal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'withdrawals')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $withdrawalMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bitcoinAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ethereumAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $litecoinAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bitcoincashAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $skrillEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paypalEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accountNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $swiftCode = null;

    #[ORM\Column(nullable: true)]
    private ?float $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fees = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $narration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
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

    public function getWithdrawalMethod(): ?string
    {
        return $this->withdrawalMethod;
    }

    public function setWithdrawalMethod(?string $withdrawalMethod): static
    {
        $this->withdrawalMethod = $withdrawalMethod;

        return $this;
    }

    public function getBitcoinAddress(): ?string
    {
        return $this->bitcoinAddress;
    }

    public function setBitcoinAddress(?string $bitcoinAddress): static
    {
        $this->bitcoinAddress = $bitcoinAddress;

        return $this;
    }

    public function getEthereumAddress(): ?string
    {
        return $this->ethereumAddress;
    }

    public function setEthereumAddress(?string $ethereumAddress): static
    {
        $this->ethereumAddress = $ethereumAddress;

        return $this;
    }

    public function getLitecoinAddress(): ?string
    {
        return $this->litecoinAddress;
    }

    public function setLitecoinAddress(?string $litecoinAddress): static
    {
        $this->litecoinAddress = $litecoinAddress;

        return $this;
    }

    public function getBitcoincashAddress(): ?string
    {
        return $this->bitcoincashAddress;
    }

    public function setBitcoincashAddress(?string $bitcoincashAddress): static
    {
        $this->bitcoincashAddress = $bitcoincashAddress;

        return $this;
    }

    public function getSkrillEmail(): ?string
    {
        return $this->skrillEmail;
    }

    public function setSkrillEmail(?string $skrillEmail): static
    {
        $this->skrillEmail = $skrillEmail;

        return $this;
    }

    public function getPaypalEmail(): ?string
    {
        return $this->paypalEmail;
    }

    public function setPaypalEmail(?string $paypalEmail): static
    {
        $this->paypalEmail = $paypalEmail;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): static
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getSwiftCode(): ?string
    {
        return $this->swiftCode;
    }

    public function setSwiftCode(?string $swiftCode): static
    {
        $this->swiftCode = $swiftCode;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFees(): ?string
    {
        return $this->fees;
    }

    public function setFees(?string $fees): static
    {
        $this->fees = $fees;

        return $this;
    }

    public function getNarration(): ?string
    {
        return $this->narration;
    }

    public function setNarration(?string $narration): static
    {
        $this->narration = $narration;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
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
