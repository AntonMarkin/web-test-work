<?php

namespace App\Controller;

use App\Entity\Url;
use App\Service\Request\UrlRequest;
use App\Service\UrlManager;
use DateInterval;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(UrlRequest $request): JsonResponse
    {
        $request->validate();
        $path = $request->getUrl();
        $lifespan = $request->getLifespan() ?? Url::ENCODED_URL_LIFESPAN;

        $date = new DateTimeImmutable();
        $expiredDate = $date->add(new DateInterval("P{$lifespan}D"));

        $entityManager = $this->getDoctrine()->getManager();
        $urlRepository = $entityManager->getRepository(Url::class);

        if (!empty($url = $urlRepository->findOneBy(['url' => $path]))) {
            if (empty ($url->getExpiredDate()) || $url->getExpiredDate() < new DateTimeImmutable()) {
                $url->setExpiredDate($expiredDate);
                $entityManager->persist($url);
                $entityManager->flush();
            }
            return $this->json([
                'hash'         => $url->getHash(),
                'expired_date' => $url->getExpiredDate()
            ]);
        }
        $url = new Url();
        $url->setExpiredDate($expiredDate);
        $url->setUrl($path);

        $entityManager->persist($url);
        $entityManager->flush();

        return $this->json([
            'hash'         => $url->getHash(),
            'expired_date' => $expiredDate
        ]);
    }

    /**
     * @Route("/decode-url", name="decode_url")
     */
    public function decodeUrl(UrlRequest $request, UrlManager $urlManager): JsonResponse
    {
        $request->validate();
        try {
            $url = $urlManager->getDecodedUrl($request->getHash());
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ]);
        }
        return $this->json([
            'url' => $url->getUrl()
        ]);
    }
    /**
     * @Route("/move-to-url", name="move_to_url")
     */
    public function moveToUrl(UrlRequest $request, UrlManager $urlManager): Response
    {
        $request->validate();
        try {
            $url = $urlManager->getDecodedUrl($request->getHash());
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ]);
        }
        return $this->redirect($url->getUrl());
    }
}
