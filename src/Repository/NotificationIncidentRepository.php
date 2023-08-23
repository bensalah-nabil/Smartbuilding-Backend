<?php

namespace App\Repository;

use App\Entity\NotificationIncident;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationIncident>
 *
 * @method NotificationIncident|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationIncident|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationIncident[]    findAll()
 * @method NotificationIncident[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationIncidentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationIncident::class);
    }

    public function save(NotificationIncident $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NotificationIncident $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getDataFromMultipleTables(): array
    {
        $queryBuilder = $this->createQueryBuilder('n');

        $queryBuilder
            ->select('ni')
            ->from(NotificationIncident::class, 'ni')
            ->orderBy('n.dateEnvoie', 'DESC');

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }
//    /**
//     * @return NotificationIncident[] Returns an array of NotificationIncident objects
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

//    public function findOneBySomeField($value): ?NotificationIncident
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
