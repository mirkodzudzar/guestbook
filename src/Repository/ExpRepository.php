<?php

namespace App\Repository;

use App\Entity\Exp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exp[]    findAll()
 * @method Exp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exp::class);
    }

    // /**
    //  * @return Exp[] Returns an array of Exp objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Exp
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
