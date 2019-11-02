<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function countByCode($code): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT count(id)
            FROM team
            WHERE code = :code
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['code' => $code]);

        return $stmt->fetchColumn();
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function findAllMyTeams($user_id)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.users', 'u')
            ->where('u.id = :user_id')
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->getResult()
            ;
    }
}
