<?php

namespace MBH\Bundle\PriceBundle\Services;


use \MBH\Bundle\PriceBundle\Document\RoomCache as RoomCacheDoc;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 *  Restriction service
 */
class Restriction
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->helper = $this->container->get('mbh.helper');
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param null $minStay
     * @param null $maxStay
     * @param null $minStayArrival
     * @param null $maxStayArrival
     * @param null $minBeforeArrival
     * @param null $maxBeforeArrival
     * @param bool $closedOnArrival
     * @param bool $closedOnDeparture
     * @param bool $closed
     * @param array $availableRoomTypes
     * @param array $availableTariffs
     * @param array $weekdays
     * @return boolean
     */
    public function update(
        \DateTime $begin,
        \DateTime $end,
        Hotel $hotel,
        $minStay = null,
        $maxStay = null,
        $minStayArrival = null,
        $maxStayArrival = null,
        $minBeforeArrival = null,
        $maxBeforeArrival = null,
        $maxGuest = null,
        $minGuest = null,
        $closedOnArrival = false,
        $closedOnDeparture = false,
        $closed = false,
        array $availableRoomTypes = [],
        array $availableTariffs = [],
        array $weekdays = []
    ) {
        $endWithDay = clone $end;
        $endWithDay->modify('+1 day');
        $restrictions = $updateCaches = $updates = $remove = [];

        (empty($availableRoomTypes)) ? $roomTypes = $hotel->getRoomTypes()->toArray() : $roomTypes = $availableRoomTypes;
        (empty($availableTariffs)) ? $tariffs = $hotel->getTariffs()->toArray() : $tariffs = $availableTariffs;

        if (
            empty($minStay) && empty($maxStay) && empty($minStayArrival) &&
            empty($maxStayArrival) && empty($minBeforeArrival) && empty($maxBeforeArrival) &&
            empty($maxGuest) && empty($minGuest) &&
            empty($closedOnArrival) && empty($closedOnDeparture) && empty($closed)
        ) {
            $empty = true;
        } else {
            $empty = false;
        }

        // find && group old caches
        $oldRestrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')
            ->fetch($begin, $end, $hotel, $this->helper->toIds($roomTypes), $this->helper->toIds($tariffs));

        foreach ($oldRestrictions as $oldRestriction) {

            if (!empty($weekdays) && !in_array($oldRestriction->getDate()->format('w'), $weekdays)) {
                continue;
            }

            $updateCaches[$oldRestriction->getDate()->format('d.m.Y')][$oldRestriction->getTariff()->getId()][$oldRestriction->getRoomType()->getId()] = $oldRestriction;

            if ($empty) {
                $remove['_id']['$in'][] = new \MongoId($oldRestriction->getId());
            } else {

                $updates[] = [
                    'criteria' => ['_id' => new \MongoId($oldRestriction->getId())],
                    'values' => [
                        'minStay' => !empty($minStay) ? (int) $minStay : null,
                        'maxStay' => !empty($maxStay) ? (int) $maxStay : null,
                        'minStayArrival' => !empty($minStayArrival) ? (int) $minStayArrival : null,
                        'maxStayArrival' => !empty($maxStayArrival) ? (int) $maxStayArrival : null,
                        'minBeforeArrival' => !empty($minBeforeArrival) ? (int) $minBeforeArrival : null,
                        'maxBeforeArrival' => !empty($maxBeforeArrival) ? (int) $maxBeforeArrival : null,
                        'maxGuest' => !empty($maxGuest) ? (int) $maxGuest : null,
                        'minGuest' => !empty($minGuest) ? (int) $minGuest : null,
                        'closedOnArrival' => !empty($closedOnArrival) ? true : false,
                        'closedOnDeparture' => !empty($closedOnDeparture) ? true : false,
                        'closed' => !empty($closed) ? true : false,
                    ]
                ];
            }
        }
        if ($empty) {
            $this->container->get('mbh.mongo')->remove('Restriction', $remove);
            return true;
        }

        foreach ($tariffs as $tariff) {
            foreach ($roomTypes as $roomType) {
                foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $endWithDay) as $date) {

                    if (isset($updateCaches[$date->format('d.m.Y')][$tariff->getId()][$roomType->getId()])) {
                        continue;
                    }
                    if (!empty($weekdays) && !in_array($date->format('w'), $weekdays)) {
                        continue;
                    }
                    $restrictions[] = [
                        'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                        'roomType' => \MongoDBRef::create('RoomTypes', new \MongoId($roomType->getId())),
                        'tariff' => \MongoDBRef::create('Tariffs', new \MongoId($tariff->getId())),
                        'date' => new \MongoDate($date->getTimestamp()),
                        'minStay' => !empty($minStay) ? (int) $minStay : null,
                        'maxStay' => !empty($maxStay) ? (int) $maxStay : null,
                        'minStayArrival' => !empty($minStayArrival) ? (int) $minStayArrival : null,
                        'maxStayArrival' => !empty($maxStayArrival) ? (int) $maxStayArrival : null,
                        'minBeforeArrival' => !empty($minBeforeArrival) ? (int) $minBeforeArrival : null,
                        'maxBeforeArrival' => !empty($maxBeforeArrival) ? (int) $maxBeforeArrival : null,
                        'maxGuest' => !empty($maxGuest) ? (int) $maxGuest : null,
                        'minGuest' => !empty($minGuest) ? (int) $minGuest : null,
                        'closedOnArrival' => !empty($closedOnArrival) ? true : null,
                        'closedOnDeparture' => !empty($closedOnDeparture) ? true : null,
                        'closed' => !empty($closed) ? true : false,
                        'isEnabled' => true
                    ];
                }
            }
        }

        $this->container->get('mbh.mongo')->batchInsert('Restriction', $restrictions);
        $this->container->get('mbh.mongo')->update('Restriction', $updates);
    }
}
