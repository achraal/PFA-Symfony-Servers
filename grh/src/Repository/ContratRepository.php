<?php

namespace App\Repository;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }
    public function countActifs(): int
{
    $now = new \DateTime();
    $qb = $this->createQueryBuilder('c');
    $qb->select('COUNT(c.id)')
       ->where('c.date_fin IS NULL OR c.date_fin > :now')
       ->setParameter('now', $now->format('Y-m-d'));
    return (int) $qb->getQuery()->getSingleScalarResult();
}

public function countExpirationProche(): int
{
    $now = new \DateTime();
    $threeMonths = (clone $now)->modify('+3 months');
    $qb = $this->createQueryBuilder('c');
    $qb->select('COUNT(c.id)')
       ->where('c.date_fin IS NOT NULL')
       ->andWhere('c.date_fin > :now')
       ->andWhere('c.date_fin <= :threeMonths')
       ->setParameter('now', $now->format('Y-m-d'))
       ->setParameter('threeMonths', $threeMonths->format('Y-m-d'));
    return (int) $qb->getQuery()->getSingleScalarResult();
}

public function getDistributionSalaires(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT 
                CASE 
                    WHEN salaire < 2000 THEN '< 2000'
                    WHEN salaire BETWEEN 2000 AND 3000 THEN '2000-3000'
                    WHEN salaire BETWEEN 3001 AND 4000 THEN '3001-4000'
                    ELSE '> 4000' END AS `range`,
                COUNT(*) as count
            FROM contrat
            GROUP BY `range`";
    $stmt = $conn->prepare($sql);
    return $stmt->executeQuery()->fetchAllAssociative();
}



    //    /**
    //     * @return Contrat[] Returns an array of Contrat objects
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

    //    public function findOneBySomeField($value): ?Contrat
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
