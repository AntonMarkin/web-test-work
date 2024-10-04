<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
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
    public function encodeUrl(Request $request): JsonResponse
    {
        $path = $request->get('url');
        $lifespan = $request->get('lifespan') ?? Url::ENCODED_URL_LIFESPAN;

        $date = new DateTimeImmutable();
        $expiredDate = $date->add(new DateInterval("P{$lifespan}D"));

        $entityManager = $this->getDoctrine()->getManager();
        $urlRepository = $entityManager->getRepository(Url::class);
        $urlEntity = new Url();

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

        $urlEntity->setExpiredDate($expiredDate);
        $urlEntity->setUrl($path);
        $url = $urlEntity;

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
    public function decodeUrl(Request $request): JsonResponse
    {
        try {
            $url = $this->getDecodedUrl($request->get('hash'));
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
    public function moveToUrl(Request $request): Response
    {
        try {
            $url = $this->getDecodedUrl($request->get('hash'));
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ]);
        }
        return $this->redirect($url->getUrl());
    }

    private function getDecodedUrl(string $hash): ?Url
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url =  $urlRepository->findOneByHash($hash);

        if (empty ($url)) {
            throw new \Exception('Non-existent hash.');
        }
        if (empty ($url->getExpiredDate()) || $url->getExpiredDate() < new DateTimeImmutable()) {
            throw new \Exception("Url's lifespan has expired.");
        }
        return $url;
    }
}
