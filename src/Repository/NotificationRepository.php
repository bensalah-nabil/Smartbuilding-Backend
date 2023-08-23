<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\NotificationIncident;
use App\Entity\NotificationReservationCantine;
use App\Entity\NotificationReservationSalle;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllDataFromMultipleTables(): array
    {
        // gets all notifications
        $entityManager = $this->getEntityManager();

        $queryNotificationIncident = $entityManager
            ->createQuery('SELECT ni FROM App\Entity\NotificationIncident ni');
        $notificationsIncident = $queryNotificationIncident->getResult();

        $queryNotificationReservationCantine = $entityManager
            ->createQuery('SELECT nrc FROM App\Entity\NotificationReservationCantine nrc');
        $notificationsReservationCantine = $queryNotificationReservationCantine->getResult();

        $queryNotificationReservationSalle = $entityManager
            ->createQuery('SELECT nrs FROM App\Entity\NotificationReservationSalle nrs');
        $notificationsReservationSalle = $queryNotificationReservationSalle->getResult();

        // Process the query results or return them
        return [
            'notificationsIncident' => $notificationsIncident,
            'notificationsReservationCantine' => $notificationsReservationCantine,
            'notificationsReservationSalle' => $notificationsReservationSalle
        ];
    }
    public function getDataFromMultipleTablesByUserId(int $userId): array
    {
        //gets user notifications for the last 30 days
        $entityManager = $this->getEntityManager();

        $thirtyDaysAgo = new DateTime();
        $thirtyDaysAgo->sub(new DateInterval('P30D'));

        $queryNotificationIncident = $entityManager->createQuery('
            SELECT ni 
            FROM App\Entity\NotificationIncident ni 
            WHERE ni.user = :user_id
            AND ni.dateEnvoie >= :thirtyDaysAgo'
        );
        $queryNotificationIncident->setParameter('user_id', $userId);
        $queryNotificationIncident->setParameter('thirtyDaysAgo', $thirtyDaysAgo);
        $notificationsIncident = $queryNotificationIncident->getResult();

        $queryNotificationReservationCantine = $entityManager->createQuery('
            SELECT nrc 
            FROM App\Entity\NotificationReservationCantine nrc 
            WHERE nrc.user = :user_id
            AND nrc.dateEnvoie >= :thirtyDaysAgo'
        );
        $queryNotificationReservationCantine->setParameter('user_id', $userId);
        $queryNotificationReservationCantine->setParameter('thirtyDaysAgo', $thirtyDaysAgo);
        $notificationsReservationCantine = $queryNotificationReservationCantine->getResult();

        $queryNotificationReservationSalle = $entityManager->createQuery('
            SELECT nrs 
            FROM App\Entity\NotificationReservationSalle nrs 
            WHERE nrs.user = :user_id
            AND nrs.dateEnvoie >= :thirtyDaysAgo'
        );
        $queryNotificationReservationSalle->setParameter('user_id', $userId);
        $queryNotificationReservationSalle->setParameter('thirtyDaysAgo', $thirtyDaysAgo);
        $notificationsReservationSalle = $queryNotificationReservationSalle->getResult();

        $result = [
            'notificationsIncident' => $notificationsIncident,
            'notificationsReservationCantine' => $notificationsReservationCantine,
            'notificationsReservationSalle' => $notificationsReservationSalle
        ];
        // Process the query results or return them
        return $result;
    }


//    public function getDataFromMultipleTables(): array
//    {
//        $entityManager = $this->getEntityManager();
//
//        $query = $entityManager->createQuery('SELECT ni FROM App\Entity\NotificationIncident ni');
//        $notifications = $query->getResult();
//
//        // Process the query result or return it
//        return $notifications;
//    }
//    /**
//     * @return Notification[] Returns an array of Notification objects
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

//    public function findOneBySomeField($value): ?Notification
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
