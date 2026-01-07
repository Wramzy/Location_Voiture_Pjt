<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La marque est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'La marque doit contenir au moins {{ limit }} caractères', maxMessage: 'La marque ne peut pas dépasser {{ limit }} caractères')]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le modèle est obligatoire')]
    #[Assert\Length(min: 1, max: 255, minMessage: 'Le modèle doit contenir au moins {{ limit }} caractère', maxMessage: 'Le modèle ne peut pas dépasser {{ limit }} caractères')]
    private ?string $modele = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'L\'année est obligatoire')]
    #[Assert\Range(min: 1900, max: 2100, notInRangeMessage: 'L\'année doit être entre {{ min }} et {{ max }}')]
    private ?int $annee = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'L\'immatriculation est obligatoire')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'L\'immatriculation doit contenir au moins {{ limit }} caractères', maxMessage: 'L\'immatriculation ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[A-Z0-9\s-]+$/i', message: 'L\'immatriculation contient des caractères invalides')]
    private ?string $immatriculation = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['disponible', 'loué', 'maintenance'], message: 'Le statut doit être disponible, loué ou maintenance')]
    private string $statut = 'disponible';

    /** @var Collection<int, Rental> */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Rental::class, cascade: ['remove'])]
    private Collection $rentals;

    public function __construct()
    {
        $this->rentals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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
            $rental->setVehicle($this);
        }

        return $this;
    }

    public function removeRental(Rental $rental): static
    {
        if ($this->rentals->removeElement($rental)) {
            if ($rental->getVehicle() === $this) {
                $rental->setVehicle(null);
            }
        }

        return $this;
    }
}
