<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 10:34
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="DishOrderItem")
 * @Gedmo\Loggable()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class DishOrderItem extends Base
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
     * @var int
     * @ODM\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="dishMenuCategories")
     * @Assert\NotNull(message="Не выбран отель")
     */
    protected $hotel;
    
    /**
     * @ODM\EmbedMany(targetDocument="DishOrderItemEmbedded" )
     *
     */
    protected $dishes;

    /**
     * @ODM\EmbedOne(targetDocument="")
     */
    protected $order;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isFreezed")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isFreezed = false;

    /**
     * @ODM\EmbedOne(targetDocument="Table")
     * @Gedmo\Versioned
     */
    protected $table;

    public function __construct()
    {
        $this->dishes = new ArrayCollection();
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
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;
    }

    /**
     * @return mixed
     */
    public function getDishes()
    {
        return $this->dishes;
    }

    /**
     * @param mixed $dishes
     */
    public function setDishes($dishes)
    {
        $this->dishes = $dishes;
    }

    public function addDishes(DishOrderItemEmbedded $dishItem)
    {
        $this->dishes->add($dishItem);
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return boolean
     */
    public function isIsFreezed()
    {
        return $this->isFreezed;
    }

    /**
     * @param boolean $isFreezed
     */
    public function setIsFreezed($isFreezed)
    {
        $this->isFreezed = $isFreezed;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    public function getPrice()
    {
        $price = 0;
        /* @var \MBH\Bundle\RestaurantBundle\Document\DishOrderItemEmbedded $dish */
        foreach ($this->getDishes() as $dish) {
            $price += $dish->getPrice();
        }
        return number_format($price,2);
    }
    

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}