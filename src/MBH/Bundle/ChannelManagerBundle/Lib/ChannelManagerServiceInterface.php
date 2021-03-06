<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerOverview;

interface ChannelManagerServiceInterface
{
    /**
     * @return array
     */
    public function getNotifications(ChannelManagerConfigInterface $config): array;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @param string $error
     * @return self
     */
    public function addError(string $error): ChannelManagerServiceInterface;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return ChannelManagerOverview
     */
    public function getOverview(\DateTime $begin, \DateTime $end, Hotel $hotel): ?ChannelManagerOverview;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null);

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages();

    /**
     * Create tariff from service
     * @param ChannelManagerConfigInterface $config
     * @param string $id
     * @return \MBH\Bundle\PriceBundle\Document\Tariff;
     * @throw \Exception
     */
    public function createTariff(ChannelManagerConfigInterface $config, $id);

    /**
     * Clear tariffs/roomTypes in config
     * @param ChannelManagerConfigInterface $config
     * @return ChannelManagerConfigInterface $config;
     */
    public function clearConfig(ChannelManagerConfigInterface $config);

    /**
     * Clear all tariffs/roomTypes in all configs
     * @return ChannelManagerConfigInterface $config;
     */
    public function clearAllConfigs();

    /**
     * Pull orders from service server
     * @return mixed
     */
    public function pullOrders();


    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config);

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config);
    
    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null);
    
    /**
     * Close all sales on service
     * @return boolean
     */
    public function closeAll();

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config);

    /**
     * User notifications
     * @param Order $order
     * @param $service
     * @param string $type
     * @return mixed
     */
    public function notify(Order $order, $service, $type = 'new');

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request);
}
