<?php

namespace App\Service\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UrlRequest extends AbstractRequest
{
    /**
     * @Assert\Type("string")
     */
    protected $url;
    /**
     * @Assert\Type("string")
     * @Assert\Length(14)
     */
    protected $hash;
    /**
     * @Assert\Type("int")
     */
    protected $lifespan;
    public function getUrl(): ?string
    {
        return $this->url;
    }
    public function getHash(): ?string
    {
        return $this->hash;
    }
    public function getLifespan(): ?string
    {
        return $this->lifespan;
    }
}