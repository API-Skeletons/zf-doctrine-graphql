<?php

namespace DbTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Artist
{
    public $id;
    public $name;
    public $createdAt;
    public $performance;
    public $user;
    public $alias;

    public function __construct()
    {
        $this->performance = new ArrayCollection();
        $this->user = new ArrayCollection();
        $this->alias = [];
    }
}
