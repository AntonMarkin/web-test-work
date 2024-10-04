<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
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
        $url = new Url();
        $url->setUrl($request->get('url'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($url);
        $entityManager->flush();

        return $this->json([
            'hash' => $url->getHash()
        ]);
    }

    /**
     * @Route("/decode-url", name="decode_url")
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        $url = $this->getUrlByHash($request->get('hash'));
        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
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
        $url = $this->getUrlByHash($request->get('hash'));
        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        return $this->redirect($url->getUrl());
    }

    private function getUrlByHash(string $hash): ?Url
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        return $urlRepository->findOneByHash($hash);
    }
}
