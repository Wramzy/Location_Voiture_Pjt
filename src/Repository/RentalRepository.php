<?php

namespace App\Repository;

use App\Entity\Rental;
use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rental>
 */
class RentalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rental::class);
    }

    /**
     * True when a vehicle already has an active rental (status en cours).
     */
    public function hasActiveRentalForVehicle(Vehicle $vehicle, ?int $excludeRentalId = null): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.vehicle = :vehicle')
            ->andWhere('r.statut = :active')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('active', 'en cours');

        if ($excludeRentalId) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeRentalId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Trouve les locations qui chevauchent avec les dates données
     */
    public function findOverlappingRentals(Vehicle $vehicle, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?int $excludeRentalId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.vehicle = :vehicle')
            ->andWhere('r.statut = :active')
            ->andWhere('(r.dateDebut <= :dateFin AND r.dateFin >= :dateDebut)')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('active', 'en cours')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        if ($excludeRentalId) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeRentalId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les locations actives pour un client
     */
    public function findActiveRentalsByClient(int $clientId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.client = :clientId')
            ->andWhere('r.statut = :active')
            ->setParameter('clientId', $clientId)
            ->setParameter('active', 'en cours')
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les locations à venir (dateDebut dans le futur)
     */
    public function findUpcomingRentals(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateDebut > :now')
            ->andWhere('r.statut = :active')
            ->setParameter('now', new \DateTime())
            ->setParameter('active', 'en cours')
            ->orderBy('r.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les locations expirées (dateFin dans le passé mais statut toujours "en cours")
     */
    public function findExpiredRentals(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateFin < :now')
            ->andWhere('r.statut = :active')
            ->setParameter('now', new \DateTime())
            ->setParameter('active', 'en cours')
            ->getQuery()
            ->getResult();
    }

    public function save(Rental $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Rental $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }
}
