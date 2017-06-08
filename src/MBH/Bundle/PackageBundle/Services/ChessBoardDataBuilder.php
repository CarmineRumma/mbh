<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Models\ChessBoard\ChessBoardUnit;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ChessBoardDataBuilder
 * @package MBH\Bundle\PackageBundle\Services
 */
class ChessBoardDataBuilder
{
    /** @var DocumentManager $dm */
    private $dm;
    /** @var  Helper $helper */
    private $helper;
    /** @var  Hotel $hotel */
    private $hotel;
    private $roomTypeIds;
    /** @var  \DateTime $beginDate */
    private $beginDate;
    /** @var  \DateTime $endDate */
    private $endDate;
    /** @var  array $housingIds */
    private $housingIds;
    /** @var  Tariff $tariff */
    private $tariff;
    /** @var  array $floorIds */
    private $floorIds;
    /** @var  ContainerInterface $container */
    private $container;
    /** @var DataCollectorTranslator $translator */
    private $translator;
    /** @var $accommodationManipulator PackageAccommodationManipulator */
    private $accommodationManipulator;
    private $pageNumber;

    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isRoomsByRoomTypeIdsInit = false;
    private $roomsByRoomTypeIds = [];
    private $isPackageAccommodationsInit = false;
    private $packageAccommodations = [];
    private $isAvailableRoomTypesInit = false;
    private $availableRoomTypes;
    const ROOM_COUNT_ON_PAGE = 30;

    /**
     * @param DocumentManager $dm
     * @param Helper $helper
     * @param PackageAccommodationManipulator $accommodationManipulator
     * @param TranslatorInterface $translator
     * @param ContainerInterface $container
     */
    public function __construct(
        DocumentManager $dm,
        Helper $helper,
        PackageAccommodationManipulator $accommodationManipulator,
        TranslatorInterface $translator,
        $container
    ) {
        $this->dm = $dm;
        $this->container = $container;
        $this->helper = $helper;
        $this->accommodationManipulator = $accommodationManipulator;
        $this->translator = $translator;
    }

    /**
     * @param Hotel $hotel
     * @param \DateTime $beginDate
     * @param \DateTime $endDate
     * @param int[] $roomTypeIds
     * @param array $housingIds
     * @param array $floorIds
     * @param Tariff $tariff
     * @param $pageNumber
     * @return ChessBoardDataBuilder
     */
    public function init(
        Hotel $hotel,
        \DateTime $beginDate,
        \DateTime $endDate,
        $roomTypeIds = [],
        array $housingIds = [],
        array $floorIds = [],
        Tariff $tariff = null,
        $pageNumber
    ) {
        $this->hotel = $hotel;
        $this->roomTypeIds = $roomTypeIds;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->housingIds = $housingIds;
        $this->tariff = $tariff;
        $this->floorIds = $floorIds;
        $this->pageNumber = $pageNumber;

        return $this;
    }

    /**
     * @return array
     */
    public function getAccommodationData()
    {
        $accommodationData = [];

        foreach ($this->getAccommodationIntervals() as $interval) {
            $accommodationData[] = $interval->__toArray();
        }

        return $this->getAccommodationIntervals();
    }

    /**
     * @return array
     */
    public function getNoAccommodationPackageIntervals()
    {
        $noAccommodationIntervals = [];
        foreach ($this->getPackagesWithoutAccommodation() as $package) {
            /** @var Package $package */
            $intervalData = $this->container->get('mbh.chess_board_unit')->setInitData($package);
            $noAccommodationIntervals[$intervalData->getId()] = $intervalData;
        }

        return array_merge($noAccommodationIntervals, $this->getDateIntervalsWithoutAccommodation());
    }

    /**
     * Получение массива данныых о количестве свободных комнат, разделенных по дням
     *
     * @return array
     */
    public function getDayNoAccommodationPackageCounts()
    {
        $counts = [];
        $roomTypes = $this->getRoomTypes();
        $daysArray = $this->getDaysArray();

        foreach ($roomTypes as $roomType) {
            foreach ($daysArray as $day) {
                $counts[$roomType->getId()][$day->format('d.m.Y')] = 0;
            }
        }

        foreach ($this->getNoAccommodationPackageIntervals() as $interval) {
            /** @var ChessBoardUnit $interval */
            $minDate = max($this->beginDate, $interval->getBeginDate());
            $maxDate = min($this->endDate, $interval->getEndDate());

            foreach (new \DatePeriod($minDate, new \DateInterval('P1D'), $maxDate) as $day) {
                /** @var \DateTime $day */
                $counts[$interval->getRoomTypeId()][$day->format('d.m.Y')]++;
            }
        }

        foreach ($counts as $roomTypeId => $roomTypeCounts) {
            $counts[$roomTypeId] = array_values($roomTypeCounts);
        }

        return $counts;
    }

    /**
     * @return Package[]
     */
    public function getPackagesWithoutAccommodation()
    {
        $packageQueryCriteria = new PackageQueryCriteria();
        $packageQueryCriteria->hotel = $this->hotel;
        //$packageQueryCriteria->confirmed
        $packageQueryCriteria->filter = 'live_between';
        $packageQueryCriteria->liveBegin = $this->beginDate;
        $packageQueryCriteria->setIsWithoutAccommodation(true);
        $packageQueryCriteria->liveEnd = $this->endDate;
        foreach ($this->getRoomTypeIds() as $roomTypeId) {
            $packageQueryCriteria->addRoomTypeCriteria($roomTypeId);
        }
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findByQueryCriteria($packageQueryCriteria);
        $orderIds = [];
        $touristIds = [];
        /** @var Package $package */
        foreach ($packages as $package) {
            /** @var PersistentCollection $tourists */
            $tourists = $package->getTourists();
            $ids = [];
            foreach ($tourists->getMongoData() as $touristData) {
                $ids[] = $touristData['$id'];
            }
            $touristIds = array_merge($touristIds, $ids);
            $orderIds[] = $package->getOrder()->getId();
        }

        $tourists = $this->dm
            ->getRepository('MBHPackageBundle:Tourist')
            ->createQueryBuilder()
            ->field('id')->in($touristIds)
            ->getQuery()
            ->execute()
            ->toArray();

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->createQueryBuilder()
            ->field('id')->in($orderIds)
            ->getQuery()
            ->execute()
            ->toArray();

        return $packages;
    }

    /**
     * Возвращает данные о периодах без размещения броней, имеющих неполное размещение,
     * ...то есть имеющих данные о размещении, но дата окончания последнего размещения меньше даты выезда брони
     *
     * @return array
     */
    private function getDateIntervalsWithoutAccommodation()
    {
        $dateIntervalsWithoutAccommodation = [];
        $packages = [];
        foreach ($this->getPackageAccommodations() as $packageAccommodation) {
            /** @var Package $package */
            $package = $packageAccommodation->getPackage();
            $packages[$package->getId()] = $package;
        }
        foreach ($packages as $package) {
            $emptyIntervals = $this->accommodationManipulator->getEmptyIntervals($package);
            foreach ($emptyIntervals as $emptyInterval) {
                $intervalData = $this->container
                    ->get('mbh.chess_board_unit')->setInitData($package, null, $emptyInterval);
                $dateIntervalsWithoutAccommodation[$intervalData->getId()] = $intervalData;
            }
        }

        return $dateIntervalsWithoutAccommodation;
    }

    /**
     * @return array
     */
    public function getAccommodationIntervals()
    {
        $accommodationIntervals = [];

        foreach ($this->getPackageAccommodations() as $accommodation) {
            /** @var PackageAccommodation $accommodation */
            $package = $accommodation->getPackage();
            $intervalData = $this->container
                ->get('mbh.chess_board_unit')->setInitData($package, $accommodation);
            $accommodationIntervals[$intervalData->getId()] = $intervalData;
        }


        return $accommodationIntervals;
    }

    /**
     * @return array
     */
    private function getPackageAccommodations()
    {
        if (!$this->isPackageAccommodationsInit) {

            $rooms = [];
            foreach ($this->getRoomsByRoomTypeIds() as $roomsByRoomTypeId) {
                $rooms = array_merge($rooms, $roomsByRoomTypeId);
            }

            if (count($rooms) > 0) {
                $accommodations = $this->dm->getRepository('MBHPackageBundle:PackageAccommodation')
                    ->fetchWithAccommodation(
                        $this->beginDate, $this->endDate, $this->helper->toIds($rooms), null, false
                    );
                //сортируем по датам начала размещения
                $this->packageAccommodations = $this->accommodationManipulator
                    ->sortAccommodationsByBeginDate($accommodations->toArray())->toArray();

                //сортируем по id брони
                usort($this->packageAccommodations, function ($a, $b) {
                    /** @var PackageAccommodation $a */
                    /** @var PackageAccommodation $b */
                    $idComparisonResult = strcmp($a->getPackage()->getId(), $b->getPackage()->getId());
                    if ($idComparisonResult < 1) {
                        return $idComparisonResult;
                    }

                    return $a->getBegin() > $b->getBegin() ? -1 : 1;
                });
            }

            $this->isPackageAccommodationsInit = true;
        }

        return $this->packageAccommodations;
    }

    /**
     * @return array [roomTypeId => date string(d.m.Y) => left rooms count]
     */
    public function getLeftRoomCounts()
    {
        $roomCacheData = [];

        /** @var array [roomTypeId => [tariffId => [date string(d.m.Y) => RoomCache]]] $roomCaches */
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch($this->beginDate,
                $this->endDate,
                $this->hotel,
                $this->getRoomTypeIds(),
                $this->tariff === null ? [] : [$this->tariff],
                true
            );

        $endDate = (clone $this->endDate)->add(new \DateInterval('P1D'));

        foreach ($this->getRoomTypes() as $roomType) {

            if (isset($roomCaches[$roomType->getId()])) {
                //Данные о комнатах могут быть получены либо для всех тарифов, и массив, содержащий их будет иметь индекс 0,
                // либо для одного и будет иметь индекс тарифа, для которого искали данные
                $roomCachesByDates = current($roomCaches[$roomType->getId()]);
                foreach (new \DatePeriod($this->beginDate, new \DateInterval('P1D'), $endDate) as $day) {
                    /** @var \DateTime $day */
                    if (isset($roomCachesByDates[$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCachesByDates[$day->format('d.m.Y')];
                        $roomCacheData[$roomType->getId()][] = $currentDateRoomCache->getLeftRooms();
                    } else {
                        $roomCacheData[$roomType->getId()][] = '';
                    }
                }
            }
        }

        return $roomCacheData;
    }

    /**
     * @return array
     */
    public function getCalendarData()
    {
        $calendarData = [];

        foreach ($this->getDaysArray() as $day) {
            /** @var \DateTime $day */
            $monthIndex = $day->format('m.Y');

            if (isset($calendarData[$monthIndex])) {
                $calendarData[$monthIndex]['daysCount']++;
            } else {
                $calendarData[$monthIndex] = [
                    'month' => $day->format('n'),
                    'year' => $day->format('Y'),
                    'daysCount' => 1
                ];
            }
        }

        return $calendarData;
    }

    /**
     * @return array
     */
    public function getDaysArray()
    {
        $days = [];
        $endDate = (clone $this->endDate)->modify('1 day');
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($this->beginDate, $interval, $endDate);
        foreach ($period as $day) {
            $days[] = $day;
        }

        return $days;
    }

    /**
     * @return array
     */
    public function getRoomTypeData()
    {
        $roomTypeData = [];
        /** @var array [roomTypeId => RoomType] $roomsByRoomTypeIds */
        $roomsByRoomTypeIds = $this->getRoomsByRoomTypeIds();

        foreach ($this->getRoomTypes() as $roomType) {

            /** @var RoomType $roomType */
            $roomTypeData[$roomType->getId()] = [
                'isEnabled' => $roomType->getIsEnabled(),
                'name' => $roomType->getName(),
                'rooms' => $this->getRoomsData($roomsByRoomTypeIds, $roomType)
            ];
        }

        return $roomTypeData;
    }

    /**
     * @return array
     */
    public function getRoomsByRoomTypeIds()
    {
        if (!$this->isRoomsByRoomTypeIdsInit) {

            $roomTypes = $this->getRoomTypeIds();
            $skipValue = ($this->pageNumber - 1) * self::ROOM_COUNT_ON_PAGE;

            $roomsByRoomTypeIds = $this->dm->getRepository('MBHHotelBundle:Room')
                ->fetch($this->hotel, $roomTypes, $this->housingIds, $this->floorIds, $skipValue,
                    self::ROOM_COUNT_ON_PAGE, true, null);

            $sortedRoomsByRoomTypeIds = [];
            foreach ($roomsByRoomTypeIds as $roomTypeId => $roomsByRoomTypeId) {
                $rooms = $roomsByRoomTypeId;
                usort($rooms, function ($first, $second) {
                    /** @var Room $first */
                    /** @var Room $second */
                    $firstRoomIntName = intval($first->getName());
                    $secondRoomIntName = intval($second->getName());

                    if (!$firstRoomIntName && is_numeric($secondRoomIntName)) {
                        return 1;
                    } elseif (is_numeric($firstRoomIntName) && !$secondRoomIntName) {
                        return -1;
                    } elseif (!$firstRoomIntName && !$secondRoomIntName) {
                        return $first->getName() > $second->getName() ? 1 : -1;
                    }

                    return $firstRoomIntName > $secondRoomIntName ? 1 : -1;
                });
                $sortedRoomsByRoomTypeIds[$roomTypeId] = $rooms;

                $this->roomsByRoomTypeIds = $sortedRoomsByRoomTypeIds;
            }

            $this->isRoomsByRoomTypeIdsInit = true;
        }

        return $this->roomsByRoomTypeIds;
    }

    /**
     * @return int
     */
    public function getRoomCount()
    {
        $roomTypes = $this->getRoomTypeIds();

        return $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetchQuery($this->hotel, $roomTypes, $this->housingIds, $this->floorIds, null, null, true)
            ->getQuery()
            ->count();
    }

    /**
     * @return array
     */
    private function getRoomTypeIds()
    {
        if (count($this->roomTypeIds) > 0) {
            return array_intersect($this->roomTypeIds, $this->getAvailableRoomTypeIds());
        }

        return $this->getAvailableRoomTypeIds();
    }

    /**
     * @param $roomsByRoomTypeIds
     * @param RoomType $roomType
     * @return array [roomTypeId => ['name', 'housing', 'floor']]
     */
    private function getRoomsData($roomsByRoomTypeIds, RoomType $roomType)
    {
        $roomsData = [];

        if (isset($roomsByRoomTypeIds[$roomType->getId()])) {
            $roomsByRoomType = $roomsByRoomTypeIds[$roomType->getId()];

            foreach ($roomsByRoomType as $room) {
                /** @var Room $room */
                //TODO: Переделать
                $houseDetails = '';
                if ($room->getHousing()) {
                    $houseDetails .= "Корпус \"" . $room->getHousing()->getName() . "\"<br>";
                }
                if ($room->getFloor()) {
                    $houseDetails .= 'Этаж ' . $room->getFloor();
                }

                $roomsData[$room->getId()] = [
                    'name' => $room->getName(),
                    'statuses' => $room->getStatus()->toArray(),
                    'houseDetails' => $houseDetails
                ];
            }
        }

        return $roomsData;
    }

    /**
     * Lazy loading of available room types
     *
     * @return RoomType[]
     */
    public function getAvailableRoomTypes()
    {
        if (!$this->isAvailableRoomTypesInit) {
            $isDisableableOn = $this->dm->getRepository('MBHClientBundle:ClientConfig')->isDisableableOn();
            $filterCollection = $this->dm->getFilterCollection();
            if ($isDisableableOn && !$filterCollection->isEnabled('disableable')) {
                $filterCollection->enable('disableable');
            }

            $this->availableRoomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->fetch($this->hotel)->toArray();

            if ($isDisableableOn && $filterCollection->isEnabled('disableable')) {
                $filterCollection->disable('disableable');
            }
            $this->isAvailableRoomTypesInit = true;
        }

        return $this->availableRoomTypes;
    }

    /**
     * @return array
     */
    private function getAvailableRoomTypeIds()
    {
        $roomTypeIds = [];
        /** @var RoomType $roomType */
        foreach ($this->getAvailableRoomTypes() as $roomType) {
            $roomTypeIds[] = $roomType->getId();
        }

        return $roomTypeIds;
    }

    /**
     * Ленивая загрузка массива объектов RoomType, используемых в данном запросе
     * @return RoomType[]
     */
    private function getRoomTypes()
    {
        if (!$this->isRoomTypesInit) {

            $this->roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->fetch($this->hotel, $this->getRoomTypeIds())->toArray();

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }
}