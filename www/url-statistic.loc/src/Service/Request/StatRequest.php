<?php

namespace App\Service\Request;

use Symfony\Component\Validator\Constraints as Assert;

class StatRequest extends AbstractRequest
{
    /**
     * @Assert\Type("string")
     * @Assert\Hostname
     */
    protected $domain;
    /**
     * @Assert\Type("string")
     */
    protected $date_start;
    /**
     * @Assert\Type("string")
     */
    protected $date_end;

    public function getDomain(): ?string
    {
        return $this->domain;
    }
    public function getDateStart(): ?string
    {
        return $this->date_start;
    }
    public function getDateEnd(): ?string
    {
        return $this->date_end;
    }
}