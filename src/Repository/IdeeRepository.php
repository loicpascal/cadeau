<?php

namespace App\Repository;

use App\Entity\Idee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Idee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Idee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Idee[]    findAll()
 * @method Idee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IdeeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Idee::class);
    }

//    /**
//     * @return Idee[] Returns an array of Idee objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Idee
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
