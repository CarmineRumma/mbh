<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\CashBundle\Document\CashDocument;
use \MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 *  ChannelManager service
 */
class Vashotel extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'VashotelConfig';

    /**
     * Update template file
     */
    const UPDATE_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:update.xml.twig';

    /**
     * Create packages template file
     */
    const CREATE_PACKAGES_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:createPackages.xml.twig';

    /**
     * Create packages template file
     */
    const GET_PACKAGES_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:getPackages.xml.twig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://www.vashotel.ru/hotel_xml/';

    private $headers = [
        'Content-type: text/xml;charset="utf-8"',
        'Accept: text/xml',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'SOAPAction: "run"'
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function createPackages()
    {
        $status = 'ok';
        $script = 'vashotel';
        $noError = true;
        $xml = simplexml_load_string($this->request->getContent());

        $config = $this->dm->getRepository('MBHChannelManagerBundle:VashotelConfig')->findOneBy([
                'hotelId' => (string) $xml->xpath('/request/hotel_id')[0]
        ]);

        if (!$config || !$this->checkResponseSignature($xml, $script, $config->getKey())) {
            return new Response(
                "ERROR"
            );
        }

        $reservationIds = [];
        $packageRepo = $this->dm->getRepository('MBHPackageBundle:Package');
        foreach ($xml->xpath('/request/notification/reservation_id') as $reservation) {
            $reservationIds[] = (string) $reservation;

            if ((string) $xml->xpath('/request/notification/notification_type')[0] == 'modify')
            {
                $packageRepo->createQueryBuilder('d')
                    ->update()
                    ->field('deletedAt')->set(new \DateTime())
                    ->field('channelManagerType')->equals('vashotel')
                    ->field('channelManagerId')->equals((string) $reservation)
                    ->getQuery()
                    ->execute();
            }

            if(count($reservationIds) >= 20) {
                $noError = $this->createPackagesFromReservationIds($reservationIds, $config);
                $reservationIds = [];
            }
        }
        $noError = $this->createPackagesFromReservationIds($reservationIds, $config);

        if (!$noError) {
            $status = 'error';
        }

        $salt = $this->container->get('mbh.helper')->getRandomString(20);
        $data = ['status' => $status, 'salt' => $salt];

        $sig = $this->getSignature(
            $this->templating->render(static::CREATE_PACKAGES_TEMPLATE, $data),
            $script,
            $config->getKey()
        );
        $data['sig'] = md5($sig);

        // response
        return new Response($this->templating->render(static::CREATE_PACKAGES_TEMPLATE, $data), 200, $this->headers);
    }

    public function createPackagesFromReservationIds($ids, VashotelConfig $config)
    {
        if (!count($ids)) {
            return true;
        }
        $script = 'get_reservations.php';
        $salt = $this->container->get('mbh.helper')->getRandomString(20);
        $data = ['config' => $config, 'salt' => $salt, 'ids' => $ids];

        $sig = $this->getSignature(
            $this->templating->render(static::GET_PACKAGES_TEMPLATE, $data),
            $script,
            $config->getKey()
        );
        $data['sig'] = md5($sig);

        $sendResult = $this->send(
            static::BASE_URL . $script,
            $this->templating->render(static::GET_PACKAGES_TEMPLATE, $data),
            $this->headers,
            true
        );

        if (!$this->checkResponse($sendResult, $script, $config->getKey())) {
            return false;
        }

        $packagesData = $this->getPackagesDataFromXml($sendResult, $config);

        if(!$packagesData) {
            return false;
        }

        // create new packages
        $packageRepo = $this->dm->getRepository('MBHPackageBundle:Package');
        foreach ($packagesData['new'] as $newInfo) {

            $package = new Package();
            $package->setBegin($newInfo['begin'])
                    ->setEnd($newInfo['end'])
                    ->setChannelManagerId($newInfo['channelManagerId'])
                    ->setChannelManagerType('vashotel')
                    ->setTariff($newInfo['tariff'])
                    ->setRoomType($newInfo['roomType'])
                    ->setAdults($newInfo['adults'])
                    ->setChildren(0)
                    ->setPrice($newInfo['price'])
                    ->setMainTourist($newInfo['mainTourist'])
                    ->setFood($newInfo['food'])
                    ->setStatus('channel_manager')
            ;

            foreach ($newInfo['tourists'] as $tourist) {
                $package->addTourist($tourist);
            }

            $errors = $this->container->get('validator')->validate($package);

            if(count($errors)) {
                throw new \Exception($errors[0]->getMessage());
            }

            $this->dm->persist($package);
            $this->dm->flush();

            if ($newInfo['type'] == 'prepayment') {
                $cashIn = new CashDocument();
                $cashIn->setMethod('electronic')
                    ->setOperation('in')
                    ->setPackage($package)
                    ->setTotal($package->getPrice())
                    ->setNote('Vashotel.ru payment' )
                    ->setIsConfirmed(true)
                ;

                $this->dm->persist($cashIn);
                $this->dm->flush();

                $cashOut = new CashDocument();
                $cashOut->setMethod('electronic')
                    ->setOperation('fee')
                    ->setPackage($package)
                    ->setTotal($newInfo['fee'])
                    ->setNote('Vashotel.ru fee' )
                    ->setIsConfirmed(true)
                ;

                $this->dm->persist($cashOut);
                $this->dm->flush();

                $package->setPaid($package->getPrice());
                $this->dm->persist($package);
                $this->dm->flush();
            }


            $this->container->get('mbh.room.cache.generator')->decrease(
                $package->getRoomType(), $package->getBegin(), $package->getEnd()
            );
        }

        // remove canceled packages
        if(!empty($packagesData['deleted'])) {
            $deletedPackages = $packageRepo->createQueryBuilder('d')
                ->field('channelManagerType')->equals('vashotel')
                ->field('channelManagerId')->in($packagesData['deleted'])
                ->getQuery()
                ->execute();

            foreach ($deletedPackages as $deletedPackage) {
                $this->dm->remove($deletedPackage);
                $this->dm->flush();

                $roomType = $deletedPackage->getRoomType();
                $begin = $deletedPackage->getBegin();
                $end = $deletedPackage->getEnd();

                $this->container->get('mbh.room.cache.generator')->increase($roomType, $begin, $end);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $script = 'set_availability.php';

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $salt = $this->container->get('mbh.helper')->getRandomString(20);
            $roomTypes = $this->getRoomTypes($config);
            $data = ['config' => $config, 'salt' => $salt, 'roomTypes' => $roomTypes];
            $result = true;

            foreach ($this->getTariffs($config) as $tariff) {

                $qb = $this->dm->getRepository('MBHPackageBundle:RoomCache')
                    ->createQueryBuilder('q')
                    ->field('roomType.id')->in(array_keys($roomTypes))
                    ->field('tariff.id')->equals($tariff['doc']->getId())
                    ->where("function() { return this.prices.length > 0; }")
                    ->sort(['roomType.id' => 'asc', 'date' => 'asc']);

                if ($begin) {
                    $qb->field('date')->gte($begin);
                }
                if ($end) {
                    $qb->field('date')->lte($end);
                }
                if ($roomType) {
                    $qb->field('roomType.id')->lte($roomType->getId());
                }

                $caches = $qb->getQuery()->execute();

                if (!count($caches)) {
                    continue;
                }

                $data['caches'] = $caches;
                $data['tariff'] = $tariff;

                $sig = $this->getSignature(
                    $this->templating->render(static::UPDATE_TEMPLATE, $data),
                    $script,
                    $config->getKey()
                );
                $data['sig'] = md5($sig);

                $sendResult = $this->send(
                    static::BASE_URL . $script,
                    $this->templating->render(static::UPDATE_TEMPLATE, $data),
                    $this->headers,
                    true
                );

                $result = $this->checkResponse($sendResult, $script, $config->getKey());
            }
        }

        return $result;
    }

    /**
     * @param mixed $xml
     * @param VashotelConfig $config
     * @return boolean|array
     */
    private function getPackagesDataFromXml($xml, VashotelConfig $config)
    {
        $result = [
            'new' => [], 'deleted' => []
        ];

        if (!$xml) {
            return $result;
        }
        if (!$xml instanceof \SimpleXMLElement) {
            $xml = simplexml_load_string($xml);
        }
        foreach ($xml->xpath('reservations/reservation') as $reservation) {

            // reservation ID
            $reservationId = (string) $reservation->attributes()['id'];

            if ((string)$reservation->xpath('status')[0] == 'canceled') {
                $result['deleted'][$reservationId] = $reservationId;
                continue;
            }

            $packages = $this->dm
                             ->getRepository('MBHPackageBundle:Package')
                             ->findBy(['channelManagerId' => $reservationId, 'channelManagerType' => 'vashotel']);
            if(count($packages)) {
                continue;
            }

            // get begin & end
            $begin = $this->helper->getDateFromString((string) $reservation->xpath('date_arrival')[0], 'Y-m-d');
            $end = $this->helper->getDateFromString((string) $reservation->xpath('date_departure')[0], 'Y-m-d');

            // get tariff
            if (!isset($reservation->xpath('rate/id')[0]) || (string) $reservation->xpath('rate/id')[0] == 'base') {
                $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($config->getHotel(), $begin);

                if (!$tariff) {
                    return false;
                }
            } else {
                if (empty($config->getTarrifsAsArray()[$reservation->xpath('rate/id')[0]])) {
                    return false;
                }
                $tariff = $config->getTarrifsAsArray( )[(string) $reservation->xpath('rate/id')[0]];
            }

            // Get main tourist
            $mainTouristName = explode(' ',  (string) trim(preg_replace('/\s+/iu', ' ', $reservation->xpath('customer/name')[0])));

            if (isset($reservation->xpath('customer/phone')[0])) {
                $mainTouristNamePhone = (string) trim(preg_replace('/\s+/iu', ' ', $reservation->xpath('customer/phone')[0]));
            }
            if (isset($reservation->xpath('customer/email')[0])) {
                $mainTouristNameEmail = (string) trim(preg_replace('/\s+/iu', ' ', $reservation->xpath('customer/email')[0]));
            }

            $mainTourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $mainTouristName[0],
                (!empty($mainTouristName[1])) ? $mainTouristName[1] : null,
                (!empty($mainTouristName[2])) ? $mainTouristName[2] : null,
                null,
                $mainTouristNameEmail,
                $mainTouristNamePhone

            );

            foreach ($reservation->xpath('rooms/room') as $room) {

                // get RoomType
                if (empty($config->getRoomsAsArray()[(string) $room->attributes()['id']])) {
                    return false;
                }
                $roomType = $config->getRoomsAsArray( )[(string) $room->attributes()['id']];

                //type
                $type = (string)$reservation->xpath('type')[0];

                // get price
                $total = 0;
                $fee = 0;
                foreach ($room->xpath('pricePerDay/price/price') as $price) {
                    $total += $price;
                }

                if ($type == 'prepayment') {
                    $percent = $this->container->getParameter('mbh.channelmanager.services')['vashotel']['fee'];
                    $total = $total/(1 - $percent);
                    $fee = $total * $percent;
                }

                //get food Type
                $food = 'BB';
                foreach ($room->xpath('pricePerDay/price/breakfast_included') as $breakfastIncluded) {
                    if($breakfastIncluded != 'yes') {
                        $food = 'RO';
                        break;
                    }
                }

                //get tourists
                $tourist = [];
                foreach ($room->xpath('guests/guest') as $guest) {

                    $tourist[] = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                        $guest->attributes()['lastname'],
                        $guest->attributes()['firstname'],
                        null, null, null, $guest->attributes()['phone']
                    );
                }

                $result['new'][$reservationId] = [
                    'channelManagerId' => $reservationId,
                    'type' => $type,
                    'begin' => $begin,
                    'end' => $end,
                    'roomType' => $roomType,
                    'mainTourist' => $mainTourist,
                    'tariff' => $tariff,
                    'price' => $total,
                    'fee' => $fee,
                    'tourists' => $tourist,
                    'adults' => (int) $room->xpath('guests_count')[0],
                    'food' => $food
                ];
            }
        }

        return $result;
    }

    /**
     * @param string $xml
     * @param string $script
     * @param string $key
     * @return bool
     */
    private function checkResponseSignature($xml, $script, $key)
    {
        if (!$xml) {
            return false;
        }
        if (!$xml instanceof \SimpleXMLElement) {
            $xml = simplexml_load_string($xml);
        }

        $responseSig = (string) $xml->xpath('sig')[0];

        $sig = $this->getSignature($xml, $script, $key);

        if (md5($sig)  !== $responseSig) {
            return false;
        }

        return true;
    }

    /**
     * @param string $response
     * @param string $script
     * @param string $key
     * @return bool
     */
    private function checkResponse($response, $script, $key)
    {
        if (!$response) {
            return false;
        }
        $xml = simplexml_load_string($response);

        if (!$this->checkResponseSignature($xml, $script, $key)) {
            return false;
        }

        return ($xml->xpath('/response/status')[0] == 'ok') ? true : false;
    }

    /**
     * @param VashotelConfig $config
     * @return array
     */
    private function getRoomTypes(VashotelConfig $config)
    {
        $result = $keys = [];

        foreach ($config->getRooms() as $room) {
            if (empty($room->getRoomId())) {
                continue;
            }
            $keys[$room->getRoomType()->getId()] = $room->getRoomId();
        }
        foreach ($config->getHotel()->getRoomTypes() as $roomType) {

            if (!isset($keys[$roomType->getId()])) {
                continue;
            }
            $result[$roomType->getId()] = [
                'id' => $keys[$roomType->getId()],
                'doc' => $roomType
            ];
        }

        return $result;
    }

    /**
     * @param VashotelConfig $config
     * @return array
     */
    private function getTariffs(VashotelConfig $config)
    {
        ($config->getIsBreakfast()) ? $food = 'BB' : $food = 'RO';

        $result = $keys = $tariffFoods = [];

        foreach ($config->getTariffs() as $tariff) {
            $keys[$tariff->getTariff()->getId()] = $tariff->getTariffId();
        }
        foreach ($config->getHotel()->getTariffs() as $tariff) {

            if (!$tariff->getIsOnline()) {
                continue;
            }
            foreach ($tariff->getFoodPrices() as $foodPrice) {
                if ($foodPrice->getPrice() !== null) {
                    $tariffFoods[] = $foodPrice->getType();
                }
            }
            $tariffFoods = array_unique($tariffFoods);

            if (!isset($keys[$tariff->getId()]) || !in_array($food, $tariffFoods)) {
                continue;
            }

            $result[] = [
                'id' => $keys[$tariff->getId()],
                'doc' => $tariff
            ];
        }

        return $result;
    }

    /**
     * @param string $xml
     * @param string $script
     * @param string $key
     * @param boolean $dev
     * @return string
     */
    private function getSignature($xml, $script = null, $key = null, $dev = false)
    {
        if (!$xml instanceof \SimpleXMLElement) {
            $xml = simplexml_load_string($xml);
        }

        $fields = $this->getXmlFieldsAsArray($xml);
        $fields = $this->sortXmlArray($fields);
        $string = $this->getStringFromXmlArray($fields, $dev);

        if ($script) {
            $string = $script . ';' . $string;
        }
        if ($key) {
            $string .= $key;
        }

        return $string;
    }

    /**
     * @param array $fields
     * @param boolean $dev
     * @return string
     */
    private function getStringFromXmlArray(array $fields, $dev = false)
    {
        $string = '';
        foreach ($fields as $field) {
            if (is_array($field['value'])) {
                $string .= $this->getStringFromXmlArray($field['value'], $dev);
            } else {
                $string .= ($dev) ? $field['name'] . '-' . $field['value'] . ';' : $field['value'] . ';' ;
            }
        }

        return $string;
    }

    /**
     * @param array $fields
     * @return array
     */
    private function sortXmlArray(array $fields)
    {
        usort(
            $fields,
            function ($a, $b) {
                return ($a['name'] < $b['name']) ? -1 : 1;
            }
        );
        foreach ($fields as $key => $field) {
            if (is_array($field['value'])) {
                $fields[$key]['value'] = $this->sortXmlArray($field['value']);
                $result[] = $this->sortXmlArray($field['value']);
            }
        }

        return $fields;
    }

    /**
     * @param string $xml
     * @return array
     */
    private function getXmlFieldsAsArray($xml)
    {
        $fields = [];
        foreach ($xml->children() as $child) {
            if (in_array($child->getName(), ['sig', 'guest'])) {
                continue;
            }

            $count = 'o';

            foreach ($fields as $field) {
                if (preg_match('/' . $child->getName() . '_sort_number_[o]*$/iu', $field['name'])) {
                    $count .= 'o';
                }
            }

            if ($child->count()) {
                $fields[] = [
                    'name' => $child->getName() . '_sort_number_' . $count,
                    'value' => $this->getXmlFieldsAsArray($child)
                ];
            } else {
                $fields[] = [
                    'name' => $child->getName() . '_sort_number_' . $count,
                    'value' => (string)$child
                ];
            }
        }

        return $fields;
    }
}