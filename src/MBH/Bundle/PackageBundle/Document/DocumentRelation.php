<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaFMS;

/**
 * Class DocumentRelation
 * @ODM\EmbeddedDocument
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentRelation
{
    /**
     * @var String
     * @ODM\String
     */
    protected $type;
    /**
     * @var VegaFMS
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaFMS")
     */
    protected $authority_organ;
    /**
     * @var String
     * @ODM\String
     */
    protected $authority;
    /**
     * @var String
     * @ODM\String
     */
    protected $series;
    /**
     * @var Integer
     * @ODM\Int
     */
    protected $number;
    /**
     * @var \DateTime
     * @ODM\Date
     */
    protected $issued;
    /**
     * @var integer
     * @ODM\Int
     */
    protected $relation;

    /**
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param String $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return VegaFMS
     */
    public function getAuthorityOrgan()
    {
        return $this->authority_organ;
    }

    /**
     * @param VegaFMS $authority_organ
     */
    public function setAuthorityOrgan(VegaFMS $authority_organ = null)
    {
        $this->authority_organ = $authority_organ;
    }

    /**
     * @return String
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * @param String $authority
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
    }

    /**
     * @return String
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param String $series
     */
    public function setSeries($series)
    {
        $this->series = $series;
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
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
    }

    /**
     * @return int
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param int $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }
}