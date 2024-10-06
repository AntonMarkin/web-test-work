<?php

namespace App\Controller;

use App\Service\Request\StatRequest;
use App\Service\UrlManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlStatsController extends AbstractController
{
    /**
     * @Route("/stats", methods={"GET"})
     */
    public function show(StatRequest $request, UrlManager $urlManager): Response
    {
        $request->validate();
        $domain = $request->getDomain();
        $dateStart = $request->getDateStart();
        $dateEnd = $request->getDateEnd();

        try {
            $stats = $urlManager->getStats($domain, $dateStart, $dateEnd);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->json($stats);
    }

    /**
     * @Route("/stats", methods={"POST"})
     */
    public function create(Request $request, UrlManager $urlManager): Response
    {
        $urls = $request->request->get('urls');

        try {
            $urlManager->saveStats($urls);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return new Response('Statistic is saved');
    }
}
