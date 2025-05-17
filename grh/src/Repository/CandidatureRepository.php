<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }
    public function countCeMois(): int
    {
        $anneeMois = (new \DateTime())->format('Y-m'); // ex: "2025-05"
        $qb = $this->createQueryBuilder('c');
        $qb->select('COUNT(c.id)')
           ->where('c.date_creation LIKE :anneeMois')
           ->setParameter('anneeMois', $anneeMois . '%');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    
    
    public function getRepartitionParPoste(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT poste as label, COUNT(*) as count FROM candidature GROUP BY poste";
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
    }
    
    


    //    /**
    //     * @return Candidature[] Returns an array of Candidature objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Candidature
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
