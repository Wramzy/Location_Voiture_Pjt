<?php

namespace App\Service;

use App\Entity\Rental;
use App\Entity\Vehicle;
use App\Repository\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer la logique métier des locations
 */
class RentalService
{
    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Vérifie si un véhicule est disponible pour une location aux dates données
     */
    public function isVehicleAvailable(Vehicle $vehicle, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?int $excludeRentalId = null): bool
    {
        // Vérifier le statut du véhicule
        if ($vehicle->getStatut() === 'loué' || $vehicle->getStatut() === 'maintenance') {
            // Si on exclut une location (édition), vérifier s'il y a d'autres locations actives
            if ($excludeRentalId && !$this->rentalRepository->hasActiveRentalForVehicle($vehicle, $excludeRentalId)) {
                return true;
            }
            return false;
        }

        // Vérifier les chevauchements de dates avec les locations existantes
        return !$this->hasDateOverlap($vehicle, $dateDebut, $dateFin, $excludeRentalId);
    }

    /**
     * Vérifie s'il y a un chevauchement de dates avec d'autres locations
     */
    public function hasDateOverlap(Vehicle $vehicle, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?int $excludeRentalId = null): bool
    {
        $overlappingRentals = $this->rentalRepository->findOverlappingRentals(
            $vehicle,
            $dateDebut,
            $dateFin,
            $excludeRentalId
        );

        return count($overlappingRentals) > 0;
    }

    /**
     * Crée une nouvelle location et met à jour le statut du véhicule
     */
    public function createRental(Rental $rental): void
    {
        // Vérifier la disponibilité
        if (!$this->isVehicleAvailable($rental->getVehicle(), $rental->getDateDebut(), $rental->getDateFin())) {
            throw new \RuntimeException('Le véhicule n\'est pas disponible pour cette période.');
        }

        // Forcer le statut à "en cours" pour une nouvelle location
        $rental->setStatut('en cours');

        // Mettre à jour le statut du véhicule
        $rental->getVehicle()?->setStatut('loué');

        $this->entityManager->persist($rental);
        $this->entityManager->flush();
    }

    /**
     * Met à jour une location existante
     */
    public function updateRental(Rental $rental): void
    {
        $vehicle = $rental->getVehicle();

        // Si la location passe à "en cours", vérifier la disponibilité
        if ($rental->getStatut() === 'en cours') {
            if (!$this->isVehicleAvailable($vehicle, $rental->getDateDebut(), $rental->getDateFin(), $rental->getId())) {
                throw new \RuntimeException('Le véhicule n\'est pas disponible pour cette période.');
            }
            $vehicle->setStatut('loué');
        } elseif ($rental->getStatut() === 'terminée') {
            // Si la location est terminée, libérer le véhicule s'il n'y a pas d'autres locations actives
            if (!$this->rentalRepository->hasActiveRentalForVehicle($vehicle, $rental->getId())) {
                $vehicle->setStatut('disponible');
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Supprime une location et met à jour le statut du véhicule si nécessaire
     */
    public function deleteRental(Rental $rental): void
    {
        $vehicle = $rental->getVehicle();
        $this->entityManager->remove($rental);
        $this->entityManager->flush();

        // Libérer le véhicule s'il n'y a pas d'autres locations actives
        if ($vehicle && !$this->rentalRepository->hasActiveRentalForVehicle($vehicle)) {
            $vehicle->setStatut('disponible');
            $this->entityManager->flush();
        }
    }

    /**
     * Calcule la durée d'une location en jours
     */
    public function calculateDuration(Rental $rental): int
    {
        if (!$rental->getDateDebut() || !$rental->getDateFin()) {
            return 0;
        }

        $diff = $rental->getDateDebut()->diff($rental->getDateFin());
        return (int) $diff->format('%a');
    }

    /**
     * Vérifie si une location est en cours (date actuelle entre dateDebut et dateFin)
     */
    public function isRentalActive(Rental $rental): bool
    {
        if (!$rental->getDateDebut() || !$rental->getDateFin()) {
            return false;
        }

        $now = new \DateTime();
        return $now >= $rental->getDateDebut() && $now <= $rental->getDateFin() && $rental->getStatut() === 'en cours';
    }
}
