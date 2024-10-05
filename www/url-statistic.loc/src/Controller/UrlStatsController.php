<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlStatsController extends AbstractController
{
    /**
     * @Route("/stats", methods={"GET"})
     */
    public function show(Request $request, UrlRepository $urlRepository): Response
    {
        $domain = $request->get('domain');
        $dateStart = $request->get('date_start');
        $dateEnd = $request->get('date_end');

        $stats = [];
        $qb = $urlRepository->createQueryBuilder('u')
            ->select('count(distinct u.url)')
        ;

        if(!empty($domain)) {
            $qb->andWhere('u.url like :domain')
                ->setParameter('domain', "%{$domain}%")
            ;
            $stats['domain'] = $domain;
        }
        if(!empty($dateStart) && !empty($dateEnd)) {
            $qb->andWhere('u.createdDate > :dateStart')
                ->andWhere('u.createdDate < :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd)
            ;
            $stats['interval'] = "$dateStart - $dateEnd";
        }
        $count = $qb->orderBy('u.url', 'ASC')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $stats['statistic'] = $count;
        return $this->json($stats);
    }

    /**
     * @Route("/stats", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $urls = $request->request->get('urls');
        $entityManager = $this->getDoctrine()->getManager();

        try {
            foreach ($urls as $url) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $url['createdDate']);

                $entity = new Url();
                $entity->setUrl($url['url']);
                $entity->setCreatedDate($date);
                $entityManager->persist($entity);
            }
            $entityManager->flush();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return new Response('Statistic is saved');
    }
}
