<?php


namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Migration
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class Visa extends Base
{
    /**
     * @var string
     * @ODM\String
     */
    protected $type;
    /**
     * @var int
     * @ODM\Int()
     */
    protected $series;
    /**
     * @var int
     * @ODM\Int()
     */
    protected $number;
    /**
     * @var \DateTime
     * @ODM\Date()
     */
    protected $issued;
    /**
     * @var \DateTime
     * @ODM\Date()
     */
    protected $expiry;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
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
     * @return \DateTime
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * @param \DateTime $issued
     * @return self
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param \DateTime $expiry
     * @return self
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }
}