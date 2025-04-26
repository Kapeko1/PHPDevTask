<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\WorkTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<WorkTime>
 */
class WorkTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkTime::class);
    }

    //    /**
    //     * @return WorkTime[] Returns an array of WorkTime objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WorkTime
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @param Employee $employee
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @return array
     */
    public function findByEmployeeAndDateRange(Employee $employee, DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $employeeId = $employee->getId();
        $qb = $this->createQueryBuilder('wt')
            ->andWhere('wt.employee = :employeeIdParam')
            ->andWhere('wt.startDay >= :startDate')
            ->andWhere('wt.startDay <= :endDate')
            ->setParameter('employeeIdParam', $employeeId, UuidType::NAME)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('wt.startTime', 'ASC');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function hasMultipleEntriesPerEmployeePerDay(Employee $employee, DateTimeImmutable $startDay): bool
    {
        $dayToCheck = $startDay->setTime(0, 0, 0);

        $count = $this->createQueryBuilder('wt')
            ->select('COUNT(wt.id)')
            ->where('wt.employee = :employeeIdParam')
            ->andWhere('wt.startDay = :startDay')
            ->setParameter('employeeIdParam', $employee->getId(), UuidType::NAME)
            ->setParameter('startDay', $dayToCheck)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count === 0;
    }

}
