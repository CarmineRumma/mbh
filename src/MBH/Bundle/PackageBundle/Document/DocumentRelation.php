<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\VegaBundle\Document\VegaFMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DocumentRelation
 * @ODM\EmbeddedDocument

 *
 * @ODM\HasLifecycleCallbacks()
 */
class DocumentRelation implements \JsonSerializable
{
    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $type;
    /**
     * @var VegaFMS
     * @ODM\ReferenceOne(name="authority_organ", targetDocument="MBH\Bundle\VegaBundle\Document\VegaFMS")
     */
    protected $authorityOrgan;
    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $authorityOrganText;
    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $authorityOrganCode;
    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $authority;
    /**
     * @var String
     * @ODM\Field(type="string") 
     */
    protected $series;
    /**
     * @var String
     * @ODM\Field(type="string")
     * @Assert\Type(type="numeric")
     */
    protected $number;
    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $issued;
    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $expiry;
    /**
     * @var string
     * @ODM\Field(type="string")
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
     * @param string $type32.4
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return VegaFMS
     */
    public function getAuthorityOrgan()
    {
        return $this->authorityOrgan;
    }

    /**
     * @param VegaFMS $authorityOrgan
     */
    public function setAuthorityOrgan(VegaFMS $authorityOrgan = null)
    {
        $this->authorityOrgan = $authorityOrgan;
    }

    /**
     * @return String
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * @return String
     */
    public function getAuthorityOrganText()
    {
        return $this->authorityOrganText;
    }

    /**
     * @param String $authorityOrganText
     * @return DocumentRelation
     */
    public function setAuthorityOrganText($authorityOrganText)
    {
        $this->authorityOrganText = $authorityOrganText;

        return $this;
    }

    /**
     * @return String
     */
    public function getAuthorityOrganCode()
    {
        return $this->authorityOrganCode;
    }

    /**
     * @param String $authorityOrganCode
     */
    public function setAuthorityOrganCode($authorityOrganCode)
    {
        $this->authorityOrganCode = $authorityOrganCode;
    }

    /**
     * @param String $authority
     * @return $this
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
        return $this;
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
     * @return $this
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
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
    public function setIssued(\DateTime $issued = null)
    {
        $this->issued = $issued;
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
     */
    public function setExpiry(\DateTime $expiry = null)
    {
        $this->expiry = $expiry;
    }

    /**
     * @return string
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param string $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }

    /**
     * @Assert\IsTrue(message = "The start date must be beforproe the end date")
     */
    public function isDateRangeValid()
    {
        return !($this->issued && $this->expiry && $this->issued->getTimestamp() > $this->expiry->getTimestamp());
    }

    public function __toString()
    {
        return $this->getAuthority() . ' ' . ($this->getIssued() ? $this->getIssued()->format('d.m.Y') : '');
    }

    /**
     * @ODM\PrePersist()
     */
    public function prePersist()
    {
        if($this->getAuthorityOrgan()) {
            $this->setAuthorityOrganCode($this->getAuthorityOrgan()->getCode());
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->getType(),
            //'authorityOrgan' => $this->getAuthorityOrgan() ? $this->getAuthorityOrgan() : null,
            'authority' => $this->getAuthority(),
            'series' => $this->getSeries(),
            'number' => $this->getNumber(),
            'issued' => $this->getIssued() ? $this->getIssued()->format('d.m.Y') : null,
            'expiry' => $this->getExpiry() ? $this->getExpiry()->format('d.m.Y') : null,
            'relation' => $this->getRelation()
        ];
    }
}