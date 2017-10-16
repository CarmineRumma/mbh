<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Migration
 * @ODM\EmbeddedDocument
 * @Gedmo\Loggable

 */
class Migration extends Base
{
    /**
     * @var int
     * @ODM\Integer()
     */
    protected $series;
    /**
     * @var int
     * @ODM\Integer()
     */
    protected $number;

    /**
     * @var string
     * @ODM\Field(type="string") 
     */
    protected $representative;

    /**
     * @var AddressObjectDecomposed
     * @ODM\EmbedOne(targetDocument="AddressObjectDecomposed")
     */
    protected $addressObjectDecomposed;

    public function __construct()
    {
        $this->addressObjectDecomposed = new AddressObjectDecomposed();
    }

    /**
     * @return int
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param int $series
     * @return self
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentative()
    {
        return $this->representative;
    }

    /**
     * @param string $representative
     * @return self
     */
    public function setRepresentative($representative)
    {
        $this->representative = $representative;
        return $this;
    }

    /**
     * @return string
     * @return self
     */
    public function getAddressObjectDecomposed()
    {
        return $this->addressObjectDecomposed;
    }

    /**
     * @param string $address
     * @return self
     */
    public function setAddressObjectDecomposed($address)
    {
        $this->addressObjectDecomposed = $address;

        return $this;
    }
}