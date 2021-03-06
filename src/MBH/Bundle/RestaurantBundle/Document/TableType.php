<?php

namespace MBH\Bundle\RestaurantBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Annotations as MBH;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="TableType")
 * @Gedmo\Loggable()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle","hotel"}, message="validator.document.category.unique")
 */

class TableType extends Base
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
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.tableType.min_name",
     *      max=100,
     *      maxMessage="validator.document.tableType.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.tableType.min_name",
     *      max=100,
     *      maxMessage="validator.document.tableType.max_name"
     * )
     */
    protected $title;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="tableTypes")
     * @Assert\NotNull(message="validator.document.tableType.hotel")
     */
    protected $hotel;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\Table", mappedBy="category", cascade={"remove"} )
     * @MBH\Versioned()
     */
    protected $categories;

    /**
     * TableType constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }
    /**
     * @return string
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * @param string $fullTitle
     */
    public function setFullTitle(string $fullTitle)
    {
        $this->fullTitle = $fullTitle;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }


    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }
    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }


}