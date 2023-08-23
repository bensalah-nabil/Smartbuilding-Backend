<?php

namespace App\Repository;

use App\Entity\ReservationCantine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationCantine>
 *
 * @method ReservationCantine|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationCantine|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationCantine[]    findAll()
 * @method ReservationCantine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationCantineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationCantine::class);
    }

    public function save(ReservationCantine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ReservationCantine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Finds reservations between two dates.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     *
     * @return ReservationCantine[]
     */
    public function findReservationsBetween(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.dateReservation BETWEEN :start_date AND :end_date')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return ReservationCantine[] Returns an array of ReservationCantine objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReservationCantine
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
