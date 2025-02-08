<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $fullname = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(length: 255)]
    private ?string $currency = null;

    #[ORM\Column(length: 255)]
    private ?string $social_account = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $social_account_contact = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'refs')]
    private ?self $ref_code = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'ref_code')]
    private Collection $refs;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $uid = null;

    #[ORM\Column(nullable: true)]
    private ?float $balance = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isVerified = null;

    #[ORM\Column(nullable: true)]
    private ?float $earning = null;

    #[ORM\Column(nullable: true)]
    private ?float $pending_withdrawal = null;

    #[ORM\Column(nullable: true)]
    private ?float $active_deposits = null;

    #[ORM\Column(nullable: true)]
    private ?float $last_deposit = null;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(targetEntity: Trade::class, mappedBy: 'user')]
    private Collection $trades;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idCardFront = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idCardBack = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationStatus = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Social>
     */
    #[ORM\OneToMany(targetEntity: Social::class, mappedBy: 'user')]
    private Collection $socials;

    /**
     * @var Collection<int, Deposit>
     */
    #[ORM\OneToMany(targetEntity: Deposit::class, mappedBy: 'user')]
    private Collection $deposits;

    /**
     * @var Collection<int, Withdrawal>
     */
    #[ORM\OneToMany(targetEntity: Withdrawal::class, mappedBy: 'user')]
    private Collection $withdrawals;

    /**
     * @var Collection<int, Signal>
     */
    #[ORM\OneToMany(targetEntity: Signal::class, mappedBy: 'user')]
    private Collection $signals;

    /**
     * @var Collection<int, Upgrade>
     */
    #[ORM\OneToMany(targetEntity: Upgrade::class, mappedBy: 'user')]
    private Collection $upgrades;

    public function __construct()
    {
        $this->refs = new ArrayCollection();
        $this->trades = new ArrayCollection();
        $this->socials = new ArrayCollection();
        $this->deposits = new ArrayCollection();
        $this->withdrawals = new ArrayCollection();
        $this->signals = new ArrayCollection();
        $this->upgrades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getSocialAccount(): ?string
    {
        return $this->social_account;
    }

    public function setSocialAccount(string $social_account): static
    {
        $this->social_account = $social_account;

        return $this;
    }

    public function getSocialAccountContact(): ?string
    {
        return $this->social_account_contact;
    }

    public function setSocialAccountContact(?string $social_account_contact): static
    {
        $this->social_account_contact = $social_account_contact;

        return $this;
    }

    public function getRefCode(): ?self
    {
        return $this->ref_code;
    }

    public function setRefCode(?self $ref_code): static
    {
        $this->ref_code = $ref_code;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRefs(): Collection
    {
        return $this->refs;
    }

    public function addRef(self $ref): static
    {
        if (!$this->refs->contains($ref)) {
            $this->refs->add($ref);
            $ref->setRefCode($this);
        }

        return $this;
    }

    public function removeRef(self $ref): static
    {
        if ($this->refs->removeElement($ref)) {
            // set the owning side to null (unless already changed)
            if ($ref->getRefCode() === $this) {
                $ref->setRefCode(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(?bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getEarning(): ?float
    {
        return $this->earning;
    }

    public function setEarning(?float $earning): static
    {
        $this->earning = $earning;

        return $this;
    }

    public function getPendingWithdrawal(): ?float
    {
        return $this->pending_withdrawal;
    }

    public function setPendingWithdrawal(?float $pending_withdrawal): static
    {
        $this->pending_withdrawal = $pending_withdrawal;

        return $this;
    }

    public function getActiveDeposits(): ?float
    {
        return $this->active_deposits;
    }

    public function setActiveDeposits(?float $active_deposits): static
    {
        $this->active_deposits = $active_deposits;

        return $this;
    }

    public function getLastDeposit(): ?float
    {
        return $this->last_deposit;
    }

    public function setLastDeposit(?float $last_deposit): static
    {
        $this->last_deposit = $last_deposit;

        return $this;
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getTrades(): Collection
    {
        return $this->trades;
    }

    public function addTrade(Trade $trade): static
    {
        if (!$this->trades->contains($trade)) {
            $this->trades->add($trade);
            $trade->setUser($this);
        }

        return $this;
    }

    public function removeTrade(Trade $trade): static
    {
        if ($this->trades->removeElement($trade)) {
            // set the owning side to null (unless already changed)
            if ($trade->getUser() === $this) {
                $trade->setUser(null);
            }
        }

        return $this;
    }

    public function getIdCardFront(): ?string
    {
        return $this->idCardFront;
    }

    public function setIdCardFront(?string $idCardFront): static
    {
        $this->idCardFront = $idCardFront;

        return $this;
    }

    public function getIdCardBack(): ?string
    {
        return $this->idCardBack;
    }

    public function setIdCardBack(?string $idCardBack): static
    {
        $this->idCardBack = $idCardBack;

        return $this;
    }

    public function getVerificationStatus(): ?string
    {
        return $this->verificationStatus;
    }

    public function setVerificationStatus(?string $verificationStatus): static
    {
        $this->verificationStatus = $verificationStatus;

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

    /**
     * @return Collection<int, Social>
     */
    public function getSocials(): Collection
    {
        return $this->socials;
    }

    public function addSocial(Social $social): static
    {
        if (!$this->socials->contains($social)) {
            $this->socials->add($social);
            $social->setUser($this);
        }

        return $this;
    }

    public function removeSocial(Social $social): static
    {
        if ($this->socials->removeElement($social)) {
            // set the owning side to null (unless already changed)
            if ($social->getUser() === $this) {
                $social->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Deposit>
     */
    public function getDeposits(): Collection
    {
        return $this->deposits;
    }

    public function addDeposit(Deposit $deposit): static
    {
        if (!$this->deposits->contains($deposit)) {
            $this->deposits->add($deposit);
            $deposit->setUser($this);
        }

        return $this;
    }

    public function removeDeposit(Deposit $deposit): static
    {
        if ($this->deposits->removeElement($deposit)) {
            // set the owning side to null (unless already changed)
            if ($deposit->getUser() === $this) {
                $deposit->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Withdrawal>
     */
    public function getWithdrawals(): Collection
    {
        return $this->withdrawals;
    }

    public function addWithdrawal(Withdrawal $withdrawal): static
    {
        if (!$this->withdrawals->contains($withdrawal)) {
            $this->withdrawals->add($withdrawal);
            $withdrawal->setUser($this);
        }

        return $this;
    }

    public function removeWithdrawal(Withdrawal $withdrawal): static
    {
        if ($this->withdrawals->removeElement($withdrawal)) {
            // set the owning side to null (unless already changed)
            if ($withdrawal->getUser() === $this) {
                $withdrawal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Signal>
     */
    public function getSignals(): Collection
    {
        return $this->signals;
    }

    public function addSignal(Signal $signal): static
    {
        if (!$this->signals->contains($signal)) {
            $this->signals->add($signal);
            $signal->setUser($this);
        }

        return $this;
    }

    public function removeSignal(Signal $signal): static
    {
        if ($this->signals->removeElement($signal)) {
            // set the owning side to null (unless already changed)
            if ($signal->getUser() === $this) {
                $signal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Upgrade>
     */
    public function getUpgrades(): Collection
    {
        return $this->upgrades;
    }

    public function addUpgrade(Upgrade $upgrade): static
    {
        if (!$this->upgrades->contains($upgrade)) {
            $this->upgrades->add($upgrade);
            $upgrade->setUser($this);
        }

        return $this;
    }

    public function removeUpgrade(Upgrade $upgrade): static
    {
        if ($this->upgrades->removeElement($upgrade)) {
            // set the owning side to null (unless already changed)
            if ($upgrade->getUser() === $this) {
                $upgrade->setUser(null);
            }
        }

        return $this;
    }
}
