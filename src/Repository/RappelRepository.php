<?php

namespace App\Repository;

use App\Entity\Rappel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rappel>
 */
class RappelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rappel::class);
    }

    public function save(Rappel $rappel): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($rappel);
        $entityManager->flush();
    }

    public function delete(Rappel $rappel): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($rappel);
        $entityManager->flush();
    }
}