<?php

namespace DbTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class User
{
    public $id;
    public $name;
    public $password;
    public $address;
    public $artist;

    public function __construct()
    {
        $this->artist = new ArrayCollection();
    }
}
