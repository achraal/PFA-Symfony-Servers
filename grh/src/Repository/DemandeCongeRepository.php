<?php

namespace App\Repository;

use App\Entity\DemandeConge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeConge>
 */
class DemandeCongeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeConge::class);
    }
    public function countApprouvesCeMois(): int
    {
        $anneeMois = (new \DateTime())->format('Y-m'); // ex: "2025-05"
        $qb = $this->createQueryBuilder('d');
        $qb->select('COUNT(d.id)')
           ->where('d.statut = :statut')
           ->andWhere('d.date_debut LIKE :anneeMois')
           ->setParameter('statut', 'Approuvé')
           ->setParameter('anneeMois', $anneeMois . '%');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    
    
    public function getTypesPlusDemandes(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT type_conge as label, COUNT(*) as count 
            FROM demande_conge 
            GROUP BY type_conge 
            ORDER BY count DESC";
    $stmt = $conn->prepare($sql);
    return $stmt->executeQuery()->fetchAllAssociative();
}

public function getCalendrierConges(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT e.nom as employe, d.date_debut as dateDebut, d.date_fin as dateFin, d.type_conge as type
            FROM demande_conge d
            JOIN employe e ON d.employe_id = e.id
            WHERE d.statut = 'Approuvé'
            ORDER BY d.date_debut";
    $stmt = $conn->prepare($sql);
    return $stmt->executeQuery()->fetchAllAssociative();
}

    


    //    /**
    //     * @return DemandeConge[] Returns an array of DemandeConge objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DemandeConge
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
