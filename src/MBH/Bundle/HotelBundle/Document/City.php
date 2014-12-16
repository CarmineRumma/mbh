<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="City")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class City extends Base
{

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;
    
    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Country", inversedBy="cities")
     * @Assert\NotNull(message="Не выбрана страна")
     */
    protected $country;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Region", inversedBy="cities")
     * @Assert\NotNull(message="Не выбран регион")
     */
    protected $region;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $title;

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set country
     *
     * @param \MBH\Bundle\HotelBundle\Document\Country $country
     * @return self
     */
    public function setCountry(\MBH\Bundle\HotelBundle\Document\Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return \MBH\Bundle\HotelBundle\Document\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set region
     *
     * @param \MBH\Bundle\HotelBundle\Document\Region $region
     * @return self
     */
    public function setRegion(\MBH\Bundle\HotelBundle\Document\Region $region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Get region
     *
     * @return \MBH\Bundle\HotelBundle\Document\Region $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}