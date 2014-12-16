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
 * @ODM\Document(collection="Country")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Country extends Base
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

    /** @ODM\ReferenceMany(targetDocument="Region", mappedBy="country") */
    protected $regions;

    /** @ODM\ReferenceMany(targetDocument="City", mappedBy="country") */
    protected $cities;

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

    public function __construct()
    {
        $this->regions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cities = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add region
     *
     * @param \MBH\Bundle\HotelBundle\Document\Region $region
     */
    public function addRegion(\MBH\Bundle\HotelBundle\Document\Region $region)
    {
        $this->regions[] = $region;
    }

    /**
     * Remove region
     *
     * @param \MBH\Bundle\HotelBundle\Document\Region $region
     */
    public function removeRegion(\MBH\Bundle\HotelBundle\Document\Region $region)
    {
        $this->regions->removeElement($region);
    }

    /**
     * Get regions
     *
     * @return \Doctrine\Common\Collections\Collection $regions
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * Add city
     *
     * @param \MBH\Bundle\HotelBundle\Document\City $city
     */
    public function addCity(\MBH\Bundle\HotelBundle\Document\City $city)
    {
        $this->cities[] = $city;
    }

    /**
     * Remove city
     *
     * @param \MBH\Bundle\HotelBundle\Document\City $city
     */
    public function removeCity(\MBH\Bundle\HotelBundle\Document\City $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection $cities
     */
    public function getCities()
    {
        return $this->cities;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}