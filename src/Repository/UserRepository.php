<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[] Returns an array of user objects
     */
    public function findNotById($id)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id <> :val')
            ->setParameter('val', $id)
            ->orderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Delete all idees related to user $id
     * @param $id
     */
    public function deleteAllIdees(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb->delete('App\Entity\Idee', 'i')
            ->where('i.user = :user_id')
            ->setParameter('user_id', $user)
            ->getQuery();
        $query->execute();
    }

    /*
    public function findOneBySomeField($value): ?user
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
