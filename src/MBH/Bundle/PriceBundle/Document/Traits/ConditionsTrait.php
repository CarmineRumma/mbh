<?php

namespace MBH\Bundle\PriceBundle\Document\Traits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class ConditionsTrait
 */
trait ConditionsTrait
{
    /**
     * @ODM\String()
     * @var string
     * @Assert\Choice(callback={"MBH\Bundle\PriceBundle\Services\PromotionConditionFactory", "getAvailableConditions"})
     */
    protected $condition;

    /**
     * @ODM\Integer()
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $conditionQuantity;

    /**
     * @ODM\String()
     * @var string
     * @Assert\Choice(callback={"MBH\Bundle\PriceBundle\Services\PromotionConditionFactory", "getAvailableConditions"})
     */
    protected $additionalCondition;

    /**
     * @ODM\Integer()
     * @var integer
     * @Assert\Type(type="numeric")
     * @Assert\Range(min="1", max="10")
     */
    protected $additionalConditionQuantity;

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param mixed $condition
     * @return Promotion
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return int
     */
    public function getConditionQuantity()
    {
        return $this->conditionQuantity;
    }

    /**
     * @param int $conditionQuantity
     * @return Promotion
     */
    public function setConditionQuantity($conditionQuantity)
    {
        $this->conditionQuantity = $conditionQuantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalCondition()
    {
        return $this->additionalCondition;
    }

    /**
     * @param string $additionalCondition
     * @return ConditionsTrait
     */
    public function setAdditionalCondition($additionalCondition)
    {
        $this->additionalCondition = $additionalCondition;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalConditionQuantity()
    {
        return $this->additionalConditionQuantity;
    }

    /**
     * @param int $additionalConditionQuantity
     * @return ConditionsTrait
     */
    public function setAdditionalConditionQuantity($additionalConditionQuantity)
    {
        $this->additionalConditionQuantity = $additionalConditionQuantity;
        return $this;
    }

}