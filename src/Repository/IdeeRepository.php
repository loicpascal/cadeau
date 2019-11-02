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

    public function findAllByUserTaking($user_id)
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.user', 'u')
            ->where('i.user_taking = :user_taking')
            ->setParameter('user_taking', $user_id)
            ->orderBy('u.firstname')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllByTeam($params)
    {
        $qb = $this->createQueryBuilder('i')
            ->innerJoin('i.team', 't')
            ->where('i.user = :user')
            ->andWhere('i.user_adding = :user_adding')
            ->andWhere('i.archived = :archived')
            ->andWhere('team_id = :team_id')
            ->setParameter('user', $params['user'])
            ->setParameter('user_adding', $params['user_adding'])
            ->setParameter('archived', $params['archived'])
            ->setParameter('team_id', $params['team_id']);

        $query = $qb->getQuery();

        return $query->getResult();
    }
}
