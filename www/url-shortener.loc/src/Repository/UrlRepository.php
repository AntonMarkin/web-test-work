<?php

namespace App\Repository;

use App\Entity\Url;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    public function findOneByHash(string $value): ?Url
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.hash = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findAllYoungerThanDate(DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdDate > :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findAllForStats(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
