<?php

namespace App\Entity;

use App\Repository\TradeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TradeRepository::class)]
class Trade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tradingType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currencyPair = null;

    #[ORM\Column(nullable: true)]
    private ?float $lotSize = null;

    #[ORM\Column(nullable: true)]
    private ?float $entryPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $stopLoss = null;

    #[ORM\Column(nullable: true)]
    private ?float $takeProfit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tradingAction = null;

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

    public function getTradingType(): ?string
    {
        return $this->tradingType;
    }

    public function setTradingType(?string $tradingType): static
    {
        $this->tradingType = $tradingType;

        return $this;
    }

    public function getCurrencyPair(): ?string
    {
        return $this->currencyPair;
    }

    public function setCurrencyPair(?string $currencyPair): static
    {
        $this->currencyPair = $currencyPair;

        return $this;
    }

    public function getLotSize(): ?float
    {
        return $this->lotSize;
    }

    public function setLotSize(?float $lotSize): static
    {
        $this->lotSize = $lotSize;

        return $this;
    }

    public function getEntryPrice(): ?float
    {
        return $this->entryPrice;
    }

    public function setEntryPrice(?float $entryPrice): static
    {
        $this->entryPrice = $entryPrice;

        return $this;
    }

    public function getStopLoss(): ?float
    {
        return $this->stopLoss;
    }

    public function setStopLoss(?float $stopLoss): static
    {
        $this->stopLoss = $stopLoss;

        return $this;
    }

    public function getTakeProfit(): ?float
    {
        return $this->takeProfit;
    }

    public function setTakeProfit(?float $takeProfit): static
    {
        $this->takeProfit = $takeProfit;

        return $this;
    }

    public function getTradingAction(): ?string
    {
        return $this->tradingAction;
    }

    public function setTradingAction(?string $tradingAction): static
    {
        $this->tradingAction = $tradingAction;

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
