<?php

namespace App\Repository;

use App\Entity\NotificationReservationCantine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationReservationCantine>
 *
 * @method NotificationReservationCantine|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationReservationCantine|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationReservationCantine[]    findAll()
 * @method NotificationReservationCantine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationReservationCantineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationReservationCantine::class);
    }

    public function save(NotificationReservationCantine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NotificationReservationCantine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return NotificationReservationCantine[] Returns an array of NotificationReservationCantine objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NotificationReservationCantine
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
