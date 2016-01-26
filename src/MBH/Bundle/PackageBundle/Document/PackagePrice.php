<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\PriceBundle\Document\Promotion;

/**
 * @ODM\EmbeddedDocument
 */
class PackagePrice
{
    /**
     * @var \DateTime
     * @ODM\Date()
     * @Assert\NotNull()
     * @Assert\Date()
     */
    protected $date;

    /**
     * @var float
     * @ODM\Float()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $price;

    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     */
    protected $tariff;

    /**
     * @var Promotion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    protected $promotion;

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function __construct(\DateTime $date, $price, Tariff $tariff, Promotion $promotion = null)
    {
        $this->setDate($date)
            ->setPrice($price)
            ->setTariff($tariff)
            ->setPromotion($promotion)
        ;
    }

    /**
     * @param \DateTime $date
     * @return PackagePrice
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return PackagePrice
     */
    public function setPrice($price)
    {
        $this->price = $price;

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
     * @param Tariff $tariff
     * @return PackagePrice
     */
    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return Promotion
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param Promotion $promotion
     * @return Promotion
     */
    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;
        return $this;
    }
}