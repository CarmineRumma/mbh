<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class RoomTypeRepository extends DocumentRepository
{
    /**
     * @param Hotel $hotel
     * @param mixed $roomTypes ids array
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(Hotel $hotel = null, $roomTypes = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');

        // hotel
        if ($hotel) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // roomTypes
        if (!empty($roomTypes) && is_array($roomTypes)) {
            $qb->field('id')->in($roomTypes);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');;

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param null $roomTypes
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $roomTypes = null)
    {
        return $this->fetchQueryBuilder($hotel, $roomTypes)->getQuery()->execute();
    }

}
