<?php

namespace App\Repository;

use App\Entity\Pilule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pilule>
 */
class PiluleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pilule::class);
    }

    //    /**
    //     * @return Pilule[] Returns an array of Pilule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Pilule
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Retourne toutes les pilules dont l'heure de prise est égale à l'heure passée en paramètre
     * @param \DateTime $dateTime Heure de prise recherchée
     * @return Pilule[] Liste des pilules concernées
     */
    public function findByHeureDePrise(\DateTime $dateTime)
    {
        $heure = $dateTime->format('H');
        $minutes = $dateTime->format('i');

        $heureEtMinutes = $heure . ':' . $minutes;

        //on récupère toutes les pilules pour lesquelles l'heure de prise (ex 22:00) est égale à l'heure passée en paramètre
        //il n'y a qu'un champ heureDePrise, qui contient l'heure et les minutes
        return $this->createQueryBuilder('p')
            ->andWhere('p.heureDePrise = :heureEtMinutes')
            ->setParameter('heureEtMinutes', $heureEtMinutes)
            ->getQuery()
            ->getResult();
    }
}
