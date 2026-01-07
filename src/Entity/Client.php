<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/u', message: 'Le nom contient des caractères invalides')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères', maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/u', message: 'Le prénom contient des caractères invalides')]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'L\'adresse doit contenir au moins {{ limit }} caractères', maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères')]
    private ?string $adresse = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Length(min: 10, max: 50, minMessage: 'Le téléphone doit contenir au moins {{ limit }} caractères', maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[0-9\s\+\-\(\)]+$/', message: 'Le format du téléphone est invalide')]
    private ?string $telephone = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le numéro de permis est obligatoire')]
    #[Assert\Length(min: 10, max: 100, minMessage: 'Le numéro de permis doit contenir au moins {{ limit }} caractères', maxMessage: 'Le numéro de permis ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[A-Z0-9]+$/', message: 'Le numéro de permis doit contenir uniquement des lettres majuscules et des chiffres')]
    private ?string $numeroPermis = null;

    /** @var Collection<int, Rental> */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Rental::class, cascade: ['remove'])]
    private Collection $rentals;

    public function __construct()
    {
        $this->rentals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getNumeroPermis(): ?string
    {
        return $this->numeroPermis;
    }

    public function setNumeroPermis(string $numeroPermis): static
    {
        $this->numeroPermis = $numeroPermis;

        return $this;
    }

    /**
     * @return Collection<int, Rental>
     */
    public function getRentals(): Collection
    {
        return $this->rentals;
    }

    public function addRental(Rental $rental): static
    {
        if (!$this->rentals->contains($rental)) {
            $this->rentals->add($rental);
            $rental->setClient($this);
        }

        return $this;
    }

    public function removeRental(Rental $rental): static
    {
        if ($this->rentals->removeElement($rental)) {
            if ($rental->getClient() === $this) {
                $rental->setClient(null);
            }
        }

        return $this;
    }
}
