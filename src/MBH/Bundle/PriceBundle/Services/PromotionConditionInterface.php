<?php

namespace MBH\Bundle\PriceBundle\Services;

use MBH\Bundle\PriceBundle\Document\Promotion;

/**
 * Interface PromotionConditionInterface
 * @package MBH\Bundle\PriceBundle\Document
 */
interface PromotionConditionInterface
{
    /**
     * @param Promotion $promotion
     * @return bool
     */
    public function isApplied(Promotion $promotion);
}