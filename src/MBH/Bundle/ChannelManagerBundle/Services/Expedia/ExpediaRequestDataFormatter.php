<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;

class ExpediaRequestDataFormatter extends AbstractRequestDataFormatter
{
    const EXPEDIA_MIN_STAY = 1;
    const EXPEDIA_MAX_STAY = 28;

    /**
     * Переопределенный метод форматирования результирующего массива данных о ценах, полученных из Maxibooking
     * @param PriceCache|null $priceCache
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param $serviceRoomTypeId
     * @param $serviceTariffId
     * @param $resultArray
     * @param \DateTime $day
     */
    protected function formatPriceData(PriceCache $priceCache,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        \DateTime $day)
    {
        if ($priceCache) {
            $priceCalculationService = $this->container->get('mbh.calculation');
            $pricesByOccupantsCount = ['prices' => $priceCalculationService->calcPrices($roomType, $tariff, $day, $day)];
        } else {
            $pricesByOccupantsCount = ['prices' => []];
        }

        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId][] = $pricesByOccupantsCount;
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления цен сервиса
     * @param $requestDataArray
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatPriceRequestData($requestDataArray, ChannelManagerConfigInterface $config)
    {
        $xmlElements = [];
        foreach ($requestDataArray as $roomTypeId => $pricesByDates) {
            $xmlRoomTypeData = new \SimpleXMLElement('AvailRateUpdate');
            foreach ($pricesByDates as $dateString => $pricesByTariffs) {
                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                $dateRangeElement->addAttribute('from', $dateString);
                $dateRangeElement->addAttribute('to', $dateString);

                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);

                foreach ($pricesByTariffs as $tariffId => $tariffPricesInfo) {

                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);

                    $rateElement = $ratePlanElement->addChild('Rate');
                    $rateElement->addAttribute('currency', $this->container->getParameter('locale.currency'));

                    foreach ($tariffPricesInfo['prices'] as $price) {
                        $perOccupancyElement = $rateElement->addChild('PerOccupancy');
                        //TODO: Доделать и проверить какие данные приходят
                        $perOccupancyElement->addAttribute('rate', $price->getPrice());
                    }
                }
            }
            $xmlElements[] = $xmlRoomTypeData;
        }

        return $this->formatTemplateRequest($xmlElements, $config)->asXML();
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления квот на комнаты
     * @param $requestDataArray
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRoomRequestData($requestDataArray, ChannelManagerConfigInterface $config)
    {
        $xmlElements = [];
        foreach ($requestDataArray as $roomTypeId => $roomQuotasByDates) {
            $xmlRoomTypeData = new \SimpleXMLElement('AvailRateUpdate');

            foreach ($roomQuotasByDates as $dateString => $roomCache) {

                /** @var RoomCache $roomCache*/
                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                $dateRangeElement->addAttribute('from', $dateString);
                $dateRangeElement->addAttribute('to', $dateString);

                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);
                $roomTypeElement->addAttribute('closed', $roomCache ? "false" : "true");

                $inventoryElement = $roomTypeElement->addChild('Inventory');
                $inventoryElement->addAttribute('totalInventoryAvailable', $roomCache ? $roomCache->getLeftRooms() : 0);
            }

            $xmlElements[] = $xmlRoomTypeData;
        }

        return $this->formatTemplateRequest($xmlElements, $config)->asXML();
    }

    /**
     * Переопределенный метод форматирования результирующего массива данных об ограничениях, полученных из Maxibooking
     * @param Restriction $restriction
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param $serviceRoomTypeId
     * @param $serviceTariffId
     * @param $resultArray
     * @param $isPriceSet
     * @param \DateTime $day
     */
    protected function formatRestrictionData(Restriction $restriction,
        RoomType $roomType,
        Tariff $tariff,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        $isPriceSet,
        \DateTime $day)
    {
        $isClosed = $restriction ? ($restriction->getClosed() || !$isPriceSet) : !$isPriceSet;

        $restrictionData = ['restriction' => $restriction, 'isClosed' => $isClosed];

        $resultArray[$serviceRoomTypeId][$day->format('Y-m-d')][$serviceTariffId][] = $restrictionData;
    }

    /**
     * Форматирование данных в формате xml, отправляемых в запросе обновления ограничений
     * @param $requestDataArray
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRestrictionRequestData($requestDataArray, ChannelManagerConfigInterface $config)
    {
        $xmlElements = [];
        foreach ($requestDataArray as $roomTypeId => $restrictionsByDates) {
            $xmlRoomTypeData = new \SimpleXMLElement('AvailRateUpdate');
            foreach ($restrictionsByDates as $dateString => $restrictionsByTariffs) {

                $dateRangeElement = $xmlRoomTypeData->addChild('DateRange');
                $dateRangeElement->addAttribute('from', $dateString);
                $dateRangeElement->addAttribute('to', $dateString);

                $roomTypeElement = $xmlRoomTypeData->addChild('RoomType');
                $roomTypeElement->addAttribute('id', $roomTypeId);

                foreach ($restrictionsByTariffs as $tariffId => $restrictionData) {

                    $ratePlanElement = $roomTypeElement->addChild('RatePlan');
                    $ratePlanElement->addAttribute('id', $tariffId);
                    $ratePlanElement->addAttribute('closed', $restrictionData['isClosed']);

                    /** @var Restriction $restriction */
                    $restriction = $restrictionData['restriction'];

                    if ($restriction) {
                        $restrictionsElement = $ratePlanElement->addChild('Restrictions');
                        //TODO: Что делать если не установлены значения?
                        //TODO: Как обрабатывать ситуации, если устанавливается значение больше 28? По дефолту будет приходить ошибка
                        $restrictionsElement->addAttribute('closedToArrival',
                            $restriction->getClosedOnArrival() ? true : false);
                        $restrictionsElement->addAttribute('closedToDeparture',
                            $restriction->getClosedOnDeparture() ? true : false);
                        $restrictionsElement->addAttribute('minLOS',
                            $restriction->getMinStay() ? $restriction->getMinStay() : self::EXPEDIA_MIN_STAY);
                        $restrictionsElement->addAttribute('maxLOS',
                            $restriction->getMaxStay() ? $restriction->getMaxStay() : self::EXPEDIA_MAX_STAY);
                    }
                }
            }
            $xmlElements[] = $xmlRoomTypeData;
        }

        return $this->formatTemplateRequest($xmlElements, $config)->asXML();
    }

    /**
     * Форматирование шаблона в формате xml, в который доблавяются данные для запросов обновления цен, ограничений и квот комнат
     * @param $elementsArray
     * @param ChannelManagerConfigInterface $config
     * @return \SimpleXMLElement
     */
    private function formatTemplateRequest($elementsArray, ChannelManagerConfigInterface $config) : \SimpleXMLElement
    {
        $requestXML = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>');

        $rootNode = $requestXML->addChild('AvailRateUpdateRQ');

        $xmlns = 'http://www.expediaconnect.com/EQC/BR/2014/01';
        $rootNode->addAttribute('xmlns', $xmlns);

        $authNode = $rootNode->addChild('Authentication');
        $authNode->addAttribute('username', $config->getUsername());
        $authNode->addAttribute('password', $config->getPassword());

        $hotelNode = $rootNode->addChild('Hotel');
        $hotelNode->addAttribute('id', $config->getHotelId());

        foreach ($elementsArray as $element) {
            $rootNode->addChild($element);
        }

        return $requestXML;
    }

    public function formatCloseForConfigData()
    {
        // TODO: Implement formatCloseForConfigData() method.
    }
}