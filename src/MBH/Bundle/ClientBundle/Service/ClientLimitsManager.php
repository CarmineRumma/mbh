<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\HttpFoundation\Session\Session;

class ClientLimitsManager
{
    private $dm;
    private $session;

    public function __construct(DocumentManager $dm, Session $session) {
        $this->dm = $dm;
        $this->session = $session;
    }

    /**
     * @param $numberOfCreatedRooms
     * @return bool
     */
    public function isLimitOfRoomsExceeded($numberOfCreatedRooms = 0)
    {
        $numberOfExistedRooms = $this->dm
            ->getRepository('MBHHotelBundle:Room')
            ->getNumberOfEnabledRooms();

        return ($numberOfCreatedRooms + $numberOfExistedRooms) > $this->getAvailableNumberOfRooms();
    }

    public function isLimitOfRoomCachesExceeded(array $modifiedRoomCaches)
    {
        $roomCacheRepository = $this->dm->getRepository('MBHPriceBundle:RoomCache');

        $date = reset($modifiedRoomCaches)->getDate();
        $roomCachesByDate = $roomCacheRepository
            ->fetch($date, $date, null, [], null)
            ->toArray();
        $roomCachesByDate = array_unique(array_merge($modifiedRoomCaches, $roomCachesByDate), SORT_REGULAR);

        $numberOfExistedRooms = 0;
        /** @var RoomCache $roomCache */
        foreach ($roomCachesByDate as $roomCache) {
            $numberOfExistedRooms += $roomCache->getTotalRooms();
        }

        return $numberOfExistedRooms > $this->getAvailableNumberOfRooms();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $rawNewRoomCachesData
     * @param array $rawUpdatedRoomCaches
     * @return array
     */
    public function getDaysWithExceededLimitNumberOfRoomsInSell(
        \DateTime $begin,
        \DateTime $end,
        array $rawNewRoomCachesData = [],
        array $rawUpdatedRoomCaches = []
    ) {
        $totalNumbersOfRoomsByDates = [];
        foreach ($rawNewRoomCachesData as $rawRoomCache) {
            /** @var \MongoDate $date */
            $date = $rawRoomCache['date'];
            $dateString = $date->toDateTime()->format('d.m.Y');
            if (isset($totalNumbersOfRoomsByDates[$dateString])) {
                $totalNumbersOfRoomsByDates[$dateString] += $rawRoomCache['totalRooms'];
            } else {
                $totalNumbersOfRoomsByDates[$dateString] = $rawRoomCache['totalRooms'];
            }
        }

        $sortedByIdsUpdatedData = [];
        foreach ($rawUpdatedRoomCaches as $updatedData) {
            $sortedByIdsUpdatedData[$updatedData['criteria']['_id']->serialize()] = $updatedData['values']['totalRooms'];
        }
        
        $rawExistedRoomCaches = $this->dm
            ->getRepository('MBHPriceBundle:RoomCache')
            ->getRawExistedRoomCaches($begin, $end, ['date', 'totalRooms']);

        foreach ($rawExistedRoomCaches as $rawRoomCache) {
            /** @var \MongoDate $mongoDate */
            $mongoDate = $rawRoomCache['date'];
            $dateString = date('d.m.Y', $mongoDate->sec);
            $rawRoomCacheId = $rawRoomCache['_id']->serialize();
            $numberOfRooms = isset($sortedByIdsUpdatedData[$rawRoomCacheId])
                ? $sortedByIdsUpdatedData[$rawRoomCacheId]
                : $rawRoomCache['totalRooms'];

            if (isset($totalNumbersOfRoomsByDates[$dateString])) {
                $totalNumbersOfRoomsByDates[$dateString] += $numberOfRooms;
            } else {
                $totalNumbersOfRoomsByDates[$dateString] = $numberOfRooms;
            }
        }
        
        $daysWithExcessNumber = [];
        foreach ($totalNumbersOfRoomsByDates as $dateString => $numberOfRooms) {
            if ($numberOfRooms > $this->getAvailableNumberOfRooms()) {
                $daysWithExcessNumber[] = $dateString;
            }
        }

        return $daysWithExcessNumber;
    }

    /**
     * @return mixed
     */
    public function getAvailableNumberOfRooms()
    {
        return $this->session->get(Client::AVAILABLE_ROOMS_LIMIT);
    }
}