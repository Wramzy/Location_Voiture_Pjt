<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function save(Vehicle $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Vehicle $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Recherche de véhicules par critères
     */
    public function search(?string $search = null, ?string $statut = null): array
    {
        $qb = $this->createQueryBuilder('v');

        if ($search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('v.marque', ':search'),
                    $qb->expr()->like('v.modele', ':search'),
                    $qb->expr()->like('v.immatriculation', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('v.statut = :statut')
               ->setParameter('statut', $statut);
        }

        return $qb->orderBy('v.marque', 'ASC')
                  ->addOrderBy('v.modele', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Trouve les véhicules disponibles
     */
    public function findAvailable(): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.statut = :statut')
            ->setParameter('statut', 'disponible')
            ->orderBy('v.marque', 'ASC')
            ->addOrderBy('v.modele', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les véhicules par statut
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('v.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
