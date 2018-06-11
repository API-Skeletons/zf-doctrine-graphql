<?php

namespace DbTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Performance
{
    public $id;
    public $performanceDate;
    public $venue;
    public $attendance;
    public $isTradable = true;
    public $ticketPrice;
    public $artist;
}
