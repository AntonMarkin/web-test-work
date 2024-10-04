<?php

namespace App\Repository;

use App\Entity\ServiceField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceField>
 *
 * @method ServiceField|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceField|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceField[]    findAll()
 * @method ServiceField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceField::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ServiceField $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(ServiceField $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
