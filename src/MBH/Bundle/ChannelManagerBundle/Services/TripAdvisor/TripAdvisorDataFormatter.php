<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Tariff;

class TripAdvisorDataFormatter
{
    /** @var  SearchFactory $search */
    private $search;
    /** @var  DocumentManager $dm */
    private $dm;

    private $isAvailableRoomTypesInit = false;
    private $availableRoomTypes;
    private $isAvailableTariffsInit = false;
    private $availableTariffs;

    public function __construct(SearchFactory $search, DocumentManager $dm)
    {
        $this->search = $search;
        $this->dm = $dm;
    }

    public function getAvailabilityData($startDate, $endDate, $hotelsSyncData)
    {
        $requestedHotelIds = [];
        foreach ($hotelsSyncData as $syncData) {
            $requestedHotelIds[] = $syncData['partner_id'];
        }
        $tripAdvisorConfigs = $this->getTripAdvisorConfigs($requestedHotelIds);

        $availabilityData = [];
        /** @var TripAdvisorConfig $tripAdvisorConfig */
        foreach ($tripAdvisorConfigs as $tripAdvisorConfig) {
            $searchResult = $this->search($startDate, $endDate, $tripAdvisorConfig->getHotel(),
                $tripAdvisorConfig->getMainTariff());
            foreach ($searchResult as $result) {
                /** @var RoomType $roomType */
                $availabilityData[$tripAdvisorConfig->getHotelId()][] = $result;
            }
        }

        return $availabilityData;
    }

    public function getTripAdvisorConfigs($tripAdvisorHotelIds = null)
    {
        $tripAdvisorConfigRepository = $this->dm->getRepository('MBHChannelManagerBundle:TripAdvisorConfig');
        if ($tripAdvisorHotelIds) {
            return $tripAdvisorConfigRepository->findAll();
        }

        return $tripAdvisorConfigRepository->createQueryBuilder()
            ->field('hotelId')->in($tripAdvisorHotelIds);
    }

    public function getBookingOptionsByHotel($startDate, $endDate, Hotel $hotel)
    {
        return $this->search($startDate, $endDate, $hotel);
    }

    public function getHotelById($hotelId)
    {
        return $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
    }

    public function getOrderById($orderId, $isWithDeleted = false)
    {
        if ($isWithDeleted) {
            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
        }

        $order = $this->dm->find('MBHPackageBundle:Order', $orderId);

        if ($isWithDeleted) {
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }
        }

        return $order;
    }

    public function getAvailableRoomTypes(Hotel $requestedHotel)
    {
        if (!$this->isAvailableRoomTypesInit) {

            $this->availableRoomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($requestedHotel);
            $this->isAvailableRoomTypesInit = true;
        }

        return $this->availableRoomTypes;
    }


    public function getAvailableTariffs(Hotel $requestedHotel, \DateTime $begin, \DateTime $end)
    {
        if (!$this->isAvailableTariffsInit) {

            $this->availableTariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->getTariffsByDates($requestedHotel, $begin, $end);
            $this->isAvailableTariffsInit = true;
        }

        return $this->availableTariffs;
    }

    public function getBookingSyncData($syncOrderData)
    {
        $syncOrders = [];
        foreach ($syncOrderData as $orderData) {
            $orderId = $orderData['reservation_id'];
            $hotelId = $orderData['partner_hotel_code'];
            $order = $this->getOrderById($orderId, true);

            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }

            $packages = $order->getPackages();

            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            $syncOrders[] = [
                'orderId' => $orderId,
                'hotelId' => $hotelId,
                'order' => $order,
                'packages' => $packages
            ];
        }

        return $syncOrders;
    }

    private function search($startDate, $endDate, Hotel $hotel, Tariff $tariff = null)
    {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = \DateTime::createFromFormat('Y-m-d' . ' H:i:s', $startDate . ' 00:00:00');
        $query->end = \DateTime::createFromFormat('Y-m-d' . ' H:i:s', $endDate . ' 00:00:00');
        $query->addHotel($hotel);
        $query->adults = 0;
        $query->children = 0;
        if ($tariff) {
            $query->tariff = $tariff;
        }

        return $this->search->setWithTariffs()->search($query);
    }
}