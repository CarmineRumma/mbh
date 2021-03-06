<?php

namespace MBH\Bundle\PriceBundle\Lib;

/**
 * Interface ConditionsInterface
 * @package MBH\Bundle\PriceBundle\Lib
 *
 */
interface ConditionsInterface
{
    /**
     * @return mixed
     */
    public function getCondition();


    /**
     * @param mixed $condition
     * @return Promotion
     */
    public function setCondition($condition);


    /**
     * @return int
     */
    public function getConditionQuantity();


    /**
     * @param int $conditionQuantity
     * @return Promotion
     */
    public function setConditionQuantity($conditionQuantity);


    /**
     * @return string
     */
    public function getAdditionalCondition();


    /**
     * @param string $additionalCondition
     * @return ConditionsTrait
     */
    public function setAdditionalCondition($additionalCondition);

    /**
     * @return int
     */
    public function getAdditionalConditionQuantity();


    /**
     * @param int $additionalConditionQuantity
     * @return ConditionsTrait
     */
    public function setAdditionalConditionQuantity($additionalConditionQuantity);

}