<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;

/**
 * Class RoomTypeReportCriteria

 */
class RoomTypeReportCriteria extends AbstractQueryCriteria
{
    /**
     * @var string
     */
    public $hotel;

    /**
     * @var string
     */
    public $roomType;

    /**
     * @var string
     */
    public $housing;

    /**
     * @var string
     */
    public $floor;

    /**
     * @var string
     * @see RoomTypeReport::getAvailableStatues()
     */
    public $status;
}