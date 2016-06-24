<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 22.06.16
 * Time: 15:11
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\RestaurantBundle\Form\DishMenuIngredientEmbeddedType;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;


/**
 * @ODM\Document(collection="DishMenuItem")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="fullTitle", message="validator.document.dishMenuItem.notunique")
 */
class DishMenuItem extends Base
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
     * @Gedmo\Versioned()
     * @ODM\ReferenceOne(targetDocument="DishMenuCategory", inversedBy="dishMenuItems")
     * @Assert\NotNull()
     */
    protected $category;

    //TODO: перевод для месаджей не забыть добавить
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое называние",
     *      max=100,
     *      maxMessage="Слишком длинное название"
     * )
     */
    protected $fullTitle = '';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $title = '';

    /**
     * @var int
     * @Gedmo\Versioned()
     * @ODM\Field(type="float", name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price = 0;

    /**
     * @var int
     * @Gedmo\Versioned()
     * @ODM\Field(type="float", name="costPrice")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $costPrice = 0;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое описание",
     *      max=300,
     *      maxMessage="Слишком длинное описание"
     * )
     */
    protected  $description;

    /**
     * @ODM\EmbedMany(targetDocument="DishMenuIngredientEmbedded")
     */
    protected $dishIngredients;

    /**
     * DishMenuItem constructor.
     *
     */
    public function __construct()
    {
        $this->dishIngredients = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCategory(): DishMenuCategory
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return $this
     */
    public function setCategory(DishMenuCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullTitle(): string
    {
        return $this->fullTitle;
    }

    /**
     * @param string $fullTitle
     * @return $this
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
        return $this;
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
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @param int $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCostPrice()
    {
        return $this->costPrice;
    }

    /**
     * @param mixed $costPrice
     * @return $this
     */
    public function setCostPrice($costPrice)
    {
        $this->costPrice = $costPrice;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getHotel()
    {
        return $this->getCategory()->getHotel();
    }

    /**
     * @return mixed
     */
    public function getDishIngredients()
    {
        return $this->dishIngredients;
    }

    /**
     * @param mixed $dishIngredients
     * @return $this
     */
    public function setDishIngredients(DishMenuIngredientEmbeddedType $dishIngredients)
    {
        $this->dishIngredients->add($dishIngredients);
        return $this;
    }


    public function addDishIngredients(DishMenuIngredientEmbedded $ingredient)
    {
        $this->dishIngredients->add($ingredient);
    }


}