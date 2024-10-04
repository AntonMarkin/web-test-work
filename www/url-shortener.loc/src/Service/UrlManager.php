<?php

namespace App\Service;

use App\Entity\Url;
use App\Repository\UrlRepository;
use DateTimeImmutable;

class UrlManager
{
    private $urlRepository;

    public function __construct(UrlRepository $urlRepository)
    {
        $this->urlRepository = $urlRepository;
    }
    public function getDecodedUrl(string $hash): ?Url
    {
        $url = $this->urlRepository->findOneByHash($hash);

        if (empty ($url)) {
            throw new \Exception('Non-existent hash.');
        }
        if (empty ($url->getExpiredDate()) || $url->getExpiredDate() < new DateTimeImmutable()) {
            throw new \Exception("Url's lifespan has expired.");
        }
        return $url;
    }
    public function getUrlsInfo(?DateTimeImmutable $date): array
    {
        if (empty($date)) {
            $urls = $this->urlRepository->findAllForStats();
        } else {
            $urls = $this->urlRepository->findAllYoungerThanDate($date);
        }

        return $urls;
    }
}