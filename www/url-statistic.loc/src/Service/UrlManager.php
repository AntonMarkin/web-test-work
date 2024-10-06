<?php

namespace App\Service;

use App\Entity\Url;
use App\Repository\UrlRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlManager
{
    private $entityManager;
    private $urlRepository;
    private $validator;

    public function __construct(ManagerRegistry $doctrine, UrlRepository $urlRepository, ValidatorInterface $validator)
    {
        $this->entityManager = $doctrine->getManager();
        $this->urlRepository = $urlRepository;
        $this->validator = $validator;
    }
    public function saveStats($urls): void
    {
        foreach ($urls as $url) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $url['createdDate']);

            $entity = new Url();
            $entity->setUrl($url['url']);
            $entity->setCreatedDate($date);

            $errors = $this->validator->validate($entity);
            if (count($errors) > 0) {
                throw new \Exception((string) $errors);
            }

            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
    public function getStats(?string $domain, ?DateTimeImmutable $dateStart, ?DateTimeImmutable $dateEnd): array
    {
        $stats = [];
        $qb = $this->urlRepository->createQueryBuilder('u')
            ->select('count(distinct u.url)')
        ;

        if(!empty($domain)) {
            $qb->andWhere('u.url like :domain')
                ->setParameter('domain', "%{$domain}%")
            ;
            $stats['domain'] = $domain;
        }
        if(!empty($dateStart) && !empty($dateEnd)) {
            $dateStart = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateStart);
            $dateEnd = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateEnd);
            $qb->andWhere('u.createdDate > :dateStart')
                ->andWhere('u.createdDate < :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd)
            ;
            $stats['interval'] = "{$dateStart} - {$dateEnd}";
        }
        $stats['url_count'] = $qb->orderBy('u.url', 'ASC')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $stats;
    }
}