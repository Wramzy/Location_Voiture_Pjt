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
