<?php

namespace App\Repository;

use App\Entity\NotificationReservationSalle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationReservationSalle>
 *
 * @method NotificationReservationSalle|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationReservationSalle|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationReservationSalle[]    findAll()
 * @method NotificationReservationSalle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationReservationSalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationReservationSalle::class);
    }

    public function save(NotificationReservationSalle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NotificationReservationSalle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return NotificationReservationSalle[] Returns an array of NotificationReservationSalle objects
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

//    public function findOneBySomeField($value): ?NotificationReservationSalle
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
