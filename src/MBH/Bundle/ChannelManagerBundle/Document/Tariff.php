<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class Tariff
{
    /**
     * @var string $tariffId
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $tariffId;

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $tariff;

    /**
     * @var string $roomType
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $roomType;

    /**
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * Get tariff
     *
     * @return \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set tariffId
     *
     * @param string $tariffId
     * @return self
     */
    public function setTariffId($tariffId)
    {
        $this->tariffId = $tariffId;
        return $this;
    }

    /**
     * Get tariffId
     *
     * @return string $tariffId
     */
    public function getTariffId()
    {
        return $this->tariffId;
    }

    /**
     * @return string
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param string $roomType
     * @return Tariff
     */
    public function setRoomType(string $roomType)
    {
        $this->roomType = $roomType;
        return $this;
    }

}