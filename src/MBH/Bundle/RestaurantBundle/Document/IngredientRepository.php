<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 01.07.16
 * Time: 14:02
 */

namespace MBH\Bundle\RestaurantBundle\Document;


class IngredientRepository extends AbstractRepository
{
    public function findIsEnabled()
    {
        return $this->createQueryBuilder()
            ->field('isEnabled')->equals('true')
            ->getQuery()
            ->execute();
    }

    /**
     * @return mixed
     */
    protected function getOwnCategoryName()
    {
        return 'MBHRestaurantBundle:IngredientCategory';
    }


}