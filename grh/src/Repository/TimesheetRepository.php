<?php

namespace App\Repository;

use App\Entity\Timesheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Timesheet>
 */
class TimesheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timesheet::class);
    }
    public function countCeMois(): int
    {
        $anneeMois = (new \DateTime())->format('Y-m'); // ex: "2025-05"
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(t.id)')
           ->where('t.date LIKE :anneeMois')
           ->setParameter('anneeMois', $anneeMois . '%');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    
    
    public function getHeuresTravailTotales(): float
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('SUM(t.heures_travail)');
        return (float) $qb->getQuery()->getSingleScalarResult();
    }
    
    public function getHeuresSupTotales(): float
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('SUM(t.heures_sup)');
        return (float) $qb->getQuery()->getSingleScalarResult();
    }
    
    public function getHeuresParEmploye(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT e.nom as nom, SUM(CAST(t.heures_travail AS UNSIGNED)) as heures
                FROM timesheet t
                JOIN employe e ON t.employe_id = e.id
                GROUP BY e.id";
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
    }
    
    


    //    /**
    //     * @return Timesheet[] Returns an array of Timesheet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Timesheet
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
