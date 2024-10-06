<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UrlRepository::class)
 */
class Url
{
    public const ENCODED_URL_LIFESPAN = 30;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=14)
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length (14)
     */
    private $hash;

    /**
     * @ORM\Column(name="created_date", type="datetime_immutable")
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\DateTime
     */
    private $createdDate;

    /**
     * @ORM\Column(name="expired_date", type="datetime", nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\DateTime
     */
    private $expiredDate;

    public function __construct()
    {
        $date = new \DateTimeImmutable();
        $this->setCreatedDate($date);
        $this->setHash($date->format('YmdHis'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getExpiredDate(): ?\DateTimeInterface
    {
        return $this->expiredDate;
    }

    public function setExpiredDate(?\DateTimeInterface $expiredDate): self
    {
        $this->expiredDate = $expiredDate;

        return $this;
    }
}
