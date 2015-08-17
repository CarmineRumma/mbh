<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\HotelBundle\Document\RoomType;

class SearchResult
{

    /**
     * @var \DateTime 
     */
    protected $begin;

    /**
     * @var \DateTime 
     */
    protected $end;

    /**
     * @var int
     */
    protected $adults;

    /**
     * @var int
     */
    protected $children;

    /**
     * @var RoomType
     */
    protected $roomType;

    /**
     * @var Tariff 
     */
    protected $tariff;

    /**
     * mixed array of prices
     * 
     * @var []
     */
    protected $prices = [];

    /**
     * mixed array of pricesByDate
     *
     * @var []
     */
    protected $pricesByDate = [];

    /**
     * @var int 
     */
    protected $roomsCount = 0;

    /**
     * @var array
     */
    protected $rooms = [];

    /**
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return $this
     */
    public function setRoomType(RoomType $roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->getNights() + 1;
    }

    /**
     * @return int
     */
    public function getNights()
    {
        return $this->end->diff($this->begin)->format("%a");
    }

    /**
     * @return int
     */
    public function getRoomsCount()
    {
        return (int) $this->roomsCount;
    }

    /**
     * @param int $roomsCount
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setRoomsCount($roomsCount)
    {
        $this->roomsCount = (int) $roomsCount;

        return $this;
    }

    /**
     * @param mixed $price
     * @param mixed $adults
     * @param mixed $children
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function addPrice($price, $adults = null, $children = null)
    {
        if ($adults !== null && $children !== null) {

            if ($this->getAdults() !== 0 || $this->getChildren() !== 0) {
                if(($adults != $this->getAdults()) || ($children != $this->getChildren())) {
                    return $this;
                }
            }
        }

        if($price === null) {
            if(isset($this->prices[$adults . '_' . $children])) {
                unset($this->prices[$adults . '_' . $children]);
            }
            return $this;
        }

        if (!isset($this->prices[$adults . '_' . $children])) {
            $this->prices[$adults . '_' . $children] = 0;
        }
        $this->prices[$adults . '_' . $children] += (float) $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setAdults($adults)
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param int $adults
     * @param int $children
     * @return int|null
     */
    public function getPrice($adults, $children)
    {
        if(!isset($this->getPrices()[$adults . '_' . $children])) {
            return null;
        }

        return (float) $this->getPrices()[$adults . '_' . $children];
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param array $prices
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @param $adults
     * @param $children
     * @return null|array
     */
    public function getPricesByDate($adults, $children)
    {
        if (isset($this->pricesByDate[$adults . '_' . $children])) {
            return $this->pricesByDate[$adults . '_' . $children];
        }

        return null;
    }

    /**
     * @param array $prices
     * @param int $adults
     * @param int $children
     * @return self
     */
    public function setPricesByDate(array $prices, $adults, $children)
    {
        $this->pricesByDate[$adults . '_' . $children] = $prices;

        return $this;
    }

    /**
     * @return array
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param array $rooms
     * @return SearchResult
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;

        return $this;
    }
}
