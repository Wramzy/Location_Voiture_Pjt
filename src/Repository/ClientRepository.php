<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function save(Client $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Client $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Recherche de clients par critères
     */
    public function search(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.nom', ':search'),
                    $qb->expr()->like('c.prenom', ':search'),
                    $qb->expr()->like('c.telephone', ':search'),
                    $qb->expr()->like('c.numeroPermis', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('c.nom', 'ASC')
                  ->addOrderBy('c.prenom', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Trouve un client par numéro de permis (unique)
     */
    public function findByNumeroPermis(string $numeroPermis): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.numeroPermis = :numeroPermis')
            ->setParameter('numeroPermis', $numeroPermis)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
