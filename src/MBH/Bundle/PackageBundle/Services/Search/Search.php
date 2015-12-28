<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;

/**
 *  Search service
 */
class Search implements SearchInterface
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
     * @var \DateTime
     */
    public $now;

    /**
     * @var RoomTypeManager
     */
    private $manager;

    /**
     * @var ClientConfig;
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->now = new \DateTime('midnight');
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);
    }

    /**
     * @param \MBH\Bundle\PackageBundle\Lib\SearchQuery $query
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult[]
     */
    public function search(SearchQuery $query)
    {
        $results = $groupedCaches = $deletedCaches = $cachesMin = $tariffMin = [];

        if (empty($query->end) || empty($query->begin) || $query->end <= $query->begin) {
            return $results;
        }

        $calc = $this->container->get('mbh.calculation');
        if (!empty($query->tariff) && !$query->tariff instanceof Tariff) {
            $query->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($query->tariff);
        }

        //promotion
        $promotion = $query->getPromotion();
        if ($promotion === null && $query->tariff && $query->tariff->getDefaultPromotion()) {
            $promotion = $query->tariff->getDefaultPromotion();
        }
        if (!$promotion) {
            $promotion = null;
        }

        // dates
        $end = clone $query->end;
        $end->modify('-1 day');
        $duration = $query->end->diff($query->begin)->format('%a');
        $today = new \DateTime('midnight');
        $beforeArrival = $today->diff($query->begin)->format('%a');
        $helper = $this->container->get('mbh.helper');

        //roomTypes
        if (empty($query->roomTypes)) {
            $query->roomTypes = [];
            foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
                $query->roomTypes = array_merge($helper->toIds($hotel->getRoomTypes()), $query->roomTypes);
            }
        } elseif ($this->manager->useCategories && !$query->forceRoomTypes) {
            $roomTypes = [];
            foreach ($query->roomTypes as $catId) {
                $cat = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory')->find($catId);
                if ($cat) {
                    $roomTypes = array_merge($helper->toIds($cat->getTypes()), $roomTypes);
                }
            }
            $query->roomTypes = count($roomTypes) ? $roomTypes : [0];
        }

        //roomCache with tariffs
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $query->begin, $end, null, $query->roomTypes
        );

        //group caches
        foreach ($roomCaches as $roomCache) {
            if ($roomCache->getTariff()) {

                if ((!empty($query->tariff) && $roomCache->getTariff()->getId() == $query->tariff->getId()) || (empty($query->tariff) && $roomCache->getTariff()->getIsDefault())) {
                    $groupedCaches['tariff'][$roomCache->getHotel()->getId()][$roomCache->getRoomType()->getId()][] = $roomCache;
                }
            } else {
                $skip = false;
                if (in_array($roomCache->getRoomType()->getId(), $query->excludeRoomTypes) && !empty($query->excludeBegin) && !empty($query->excludeEnd) && $roomCache->getDate() >= $query->excludeBegin && $roomCache->getDate() <= $query->excludeEnd) {
                    $skip = true;
                }

                if ($skip || ($roomCache->getLeftRooms() > 0 && $roomCache->getRoomType()->getTotalPlaces() >= $query->getTotalPlaces() && !$roomCache->getIsClosed())) {
                    $groupedCaches['room'][$roomCache->getHotel()->getId()][$roomCache->getRoomType()->getId()][] = $roomCache;
                }
            }
        }

        if (!isset($groupedCaches['room'])) {
            return $results;
        }

        //tariff dates
        if (!empty($query->tariff)) {

            if ($query->tariff->getBegin() && $query->tariff->getBegin() > $this->now) {
                return $results;
            }
            if ($query->tariff->getEnd() && $query->tariff->getEnd() < $this->now) {
                return $results;
            }
        }

        //delete short cache chains
        foreach ($groupedCaches['room'] as $hotelId => $hotelA) {
            foreach ($hotelA as $roomTypeId => $caches) {

                $quotes = false;
                if (isset($groupedCaches['tariff'][$hotelId][$roomTypeId])) {
                    foreach ($groupedCaches['tariff'][$hotelId][$roomTypeId] as $tariffCache) {

                        if (!isset($tariffMin[$hotelId][$roomTypeId]) || $tariffMin[$hotelId][$roomTypeId] > $tariffCache->getLeftRooms()) {
                            $tariffMin[$hotelId][$roomTypeId] = $tariffCache->getLeftRooms();
                        }

                        $skip = false;
                        if (in_array($tariffCache->getRoomType()->getId(), $query->excludeRoomTypes) && !empty($query->excludeBegin) && !empty($query->excludeEnd) && $tariffCache->getDate() >= $query->excludeBegin && $tariffCache->getDate() <= $query->excludeEnd) {
                            $skip = true;
                        }

                        if ($tariffCache->getLeftRooms() <= 0 && !$skip) {
                            $quotes = true;
                        }
                    }
                }
                if (count($caches) == $duration && !$quotes) {
                    $deletedCaches[$hotelId][$roomTypeId] = $caches;
                }
            }
        }

        //restrictions
        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $query->begin, $query->end, null, $query->roomTypes
        );

        foreach ($restrictions as $restriction) {
            $delete = false;

            if ($query->tariff && $query->tariff->getId() != $restriction->getTariff()->getId()) {
                continue;
            }
            if (!$query->tariff && !$restriction->getTariff()->getIsDefault()) {
                continue;
            }

            //ClosedOnDeparture
            if ($restriction->getDate()->format('d.m.Y') == $query->end->format('d.m.Y')) {
                if ($restriction->getClosedOnDeparture() && isset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()])) {
                    unset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()]);
                }
                continue;
            }
            //MinBeforeArrival
            if ($restriction->getMinBeforeArrival() && $beforeArrival < $restriction->getMinBeforeArrival()) {
                $delete = true;
            }
            //MaxBeforeArrival
            if ($restriction->getMaxBeforeArrival() && $beforeArrival > $restriction->getMaxBeforeArrival()) {
                $delete = true;
            }
            //MinStay
            if ($restriction->getMinStay() && $duration < $restriction->getMinStay()) {
                $delete = true;
            }
            //MinStay
            if ($restriction->getMaxStay() && $duration > $restriction->getMaxStay()) {
                $delete = true;
            }
            //MinStayArrival
            if ($restriction->getMinStayArrival() && $restriction->getDate()->format('d.m.Y') == $query->begin->format('d.m.Y') && $duration < $restriction->getMinStayArrival()) {
                $delete = true;
            }
            //MaxStayArrival
            if ($restriction->getMaxStayArrival() && $restriction->getDate()->format('d.m.Y') == $query->begin->format('d.m.Y') && $duration > $restriction->getMaxStayArrival()) {
                $delete = true;
            }
            //ClosedOnArrival
            if ($restriction->getClosedOnArrival() && $restriction->getDate()->format('d.m.Y') == $query->begin->format('d.m.Y')) {
                $delete = true;
            }
            //closed
            if ($restriction->getClosed()) {
                $delete = true;
            }

            //delete chain
            if ($delete && isset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()])) {
                unset($deletedCaches[$restriction->getHotel()->getId()][$restriction->getRoomType()->getId()]);
            }
        }

        //cacheMin
        foreach ($deletedCaches as $hotelId => $hotelArray) {
            foreach ($hotelArray as $roomTypeId => $caches) {
                foreach ($caches as $cache) {
                    if (!isset($cachesMin[$hotelId][$roomTypeId]) || $cachesMin[$hotelId][$roomTypeId] > $cache->getLeftRooms()) {
                        $cachesMin[$hotelId][$roomTypeId] = $cache->getLeftRooms();
                    }
                }
            }
        }

        //generate result
        foreach ($deletedCaches as $hotelId => $hotelArray) {

            //skip disabled tariff & hotel
            $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($hotelId);
            if (!$hotel || !$hotel->getIsEnabled()) {
                continue;
            }
            if (!empty($query->tariff)) {
                $tariff = $query->tariff;
            } else {
                $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);
            }
            if (!$tariff || !$tariff->getIsEnabled()) {
                continue;
            }
            // check hotel permission
            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
                continue;
            }

            foreach ($hotelArray as $roomTypeId => $caches) {

                $min = $cachesMin[$hotelId][$roomTypeId];

                if (isset($tariffMin[$hotelId][$roomTypeId]) && $tariffMin[$hotelId][$roomTypeId] < $min) {
                    $min = $tariffMin[$hotelId][$roomTypeId];
                }

                //filter infants
                if (!empty($query->childrenAges)) {
                    foreach ($query->childrenAges as $age) {
                        if ($age <= $tariff->getInfantAge()) {
                            $query->children -= 1;
                        }
                    }
                }

                //filter children
                if (!empty($query->childrenAges)) {
                    foreach ($query->childrenAges as $age) {
                        if ($age > $tariff->getChildAge()) {
                            $query->children -= 1;
                            $query->adults += 1;
                        }
                    }
                }

                $roomType = $caches[0]->getRoomType();
                $useCategories = $query->isOnline && $this->config && $this->config->getUseRoomTypeCategory();
                $result = new SearchResult();
                $tourists = $roomType->getAdultsChildrenCombination($query->adults, $query->children);

                if ($query->accommodations) {
                    $groupedRooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetchAccommodationRooms(
                        $query->begin,
                        $query->end,
                        $roomType->getHotel(),
                        $roomType->getId()
                    );
                    $result->setRooms($groupedRooms);
                }

                $result->setBegin($query->begin)
                    ->setEnd($query->end)
                    ->setRoomType($roomType)
                    ->setTariff($tariff)
                    ->setRoomsCount($min)
                    ->setAdults($tourists['adults'])
                    ->setChildren($tourists['children'])
                    ->setUseCategories($useCategories);

                //prices
                $prices = $calc->calcPrices($roomType, $tariff, $query->begin, $end, $tourists['adults'], $tourists['children'], $promotion);

                if (!$prices || (($query->adults + $query->children) != 0 && !isset($prices[$tourists['adults'] . '_' . $tourists['children']]))) {
                    continue;
                }
                foreach ($prices as $price) {
                    $result->addPrice($price['total'], $price['adults'], $price['children'])
                        ->setPricesByDate($price['prices'], $price['adults'], $price['children'])
                        ->setPackagePrices($price['packagePrices'], $price['adults'], $price['children'])
                    ;
                }
                if (empty($result->getPrices())) {
                    continue;
                }

                //check windows
                if (!$this->checkWindows($result)) {
                    continue;
                }

                $roomTypeObjId = $result->getRoomTypeInterfaceObject()->getId() . '_' . $result->getTariff()->getId();

                if (isset($results[$roomTypeObjId])) {
                    $totalRooms = $result->getRoomsCount() + $results[$roomTypeObjId]->getRoomsCount();
                    $result->setRoomsCount($totalRooms);
                    $results[$roomTypeObjId]->setRoomsCount($totalRooms);
                }

                if (
                    !$result->isUseCategories() ||
                    !isset($results[$roomTypeObjId]) ||
                    $results[$roomTypeObjId]->getRoomType()->getTotalPlaces() > $result->getRoomType()->getTotalPlaces()
                ) {
                    $results[$roomTypeObjId] = $result;
                }
            }
        }
        sort($results);
        return $results;
    }

    public function checkWindows(SearchResult $result)
    {
        if (!$this->config || !$this->config->getSearchWindows()) {
            return true;
        }

        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $result->getBegin(),
            $result->getBegin(),
            null,
            [$result->getRoomType()->getId()],
            [$result->getTariff()->getId()]
        )->toArray();

        if (!count($restrictions)) {
            return true;
        }

        $restriction = array_values($restrictions)[0];

        if ($restriction->getDate() != $result->getBegin() || !$restriction->getMinStayArrival()) {
            return true;
        }

        $endWindow = clone $result->getEnd();
        $endWindow->modify('+' . $restriction->getMinStayArrival() . ' days');

        //check right window
        if (!$this->checkWindow($result->getEnd(), $endWindow, $result, $restriction->getMinStayArrival())) {
            return false;
        }

        //check left window
        if ($result->getBegin() != new \DateTime('midnight')) {
            $beginWindow = clone $result->getBegin();
            $beginWindow->modify('-' . $restriction->getMinStayArrival() . ' days');
            if (!$this->checkWindow(
                $beginWindow, $result->getBegin(), $result, $restriction->getMinStayArrival(), false
            )
            ) {
                return false;
            }
        }

        return true;
    }

    public function checkWindow(\DateTime $begin, \DateTime $end, SearchResult $result, $len, $right = true)
    {
        $roomTypeId = $result->getRoomType()->getId();
        $tariffId = $result->getTariff()->getId();

        $caches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin, $end, null, [$roomTypeId], null
        );
        $tariffCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin, $end, null, [$roomTypeId], [$tariffId], true
        );

        if (count($caches) < $len + 1) {
            return true;
        }

        $switchCount = $ascCount = $descCount = 0;
        $prevNum = null;
        foreach ($caches as $cache) {
            $strDate = $cache->getDate()->format('d.m.Y');
            if (isset($tariffCaches[$roomTypeId][$tariffId][$strDate])) {
                $cache = $tariffCaches[$roomTypeId][$tariffId][$strDate];
            }

            $num = $cache->getLeftRooms();

            if ($right && $cache->getDate() == $begin) {
                $num--;
            }
            if (!$right && $cache->getDate() == $end) {
                $num--;
            }
            if ($prevNum !== null && $num != $prevNum) {

                $switchCount++;

                if ($num > $prevNum) {
                    $ascCount++;
                }
                if ($num < $prevNum) {
                    $descCount++;
                }
            }
            $prevNum = $num;
        }
        if ($switchCount > 1 && $descCount > 0 && $ascCount > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        $tariffs = $results = [];
        if (!empty($query->roomTypes)) {
            $roomTypes = $this->manager->getRooms(null, $query->roomTypes);
            foreach ($roomTypes as $roomType) {
                $tariffs = array_merge($tariffs, $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch($roomType->getHotel())->toArray());
            }
        } else {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch(null, null, true);
        }

        foreach ($tariffs as $tariff) {

            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
            if ($tariff->getHotel()->getDeletedAt()) {
                continue;
            }
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
                continue;
            }

            if ($tariff->getBegin() && $tariff->getBegin() > $this->now) {
                continue;
            }
            if ($tariff->getEnd() && $tariff->getEnd() < $this->now) {
                continue;
            }
            if ($query->isOnline && !$tariff->getIsOnline()) {
                continue;
            }
            if ($query->grouped) {
                $results[$tariff->getHotel()->getId()][] = $tariff;
            } else {
                $results[] = $tariff;
            }
        }

        return $results;
    }

}
