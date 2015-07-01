<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Form\RoomCacheGeneratorType;

/**
 * @Route("room_cache")
 */
class RoomCacheController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="room_cache_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function indexAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        return [
            'roomTypes' => $hotel->getRoomTypes(),
            'tariffs' => $hotel->getTariffs(),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="room_cache_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function tableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if(!$begin) {
            $begin = new \DateTime('00:00');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if(!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+45 days');
        }

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin' => $begin,
            'end' => $end,
            'hotel' => $hotel
        ];

        //get roomTypes
        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
            ->fetch($hotel, $request->get('roomTypes'))
        ;
        if (!count($roomTypes)) {
            return array_merge($response, ['error' => 'Типы номеров не найдены']);
        }
        //get tariffs
        if (!empty($request->get('tariffs'))) {
            $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
                ->fetch($hotel, $request->get('tariffs'))
            ;
        } else {
            $tariffs = [null];
        }

        //get roomCaches
        $roomCaches = $dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin, $end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                false, true)
        ;

        return array_merge($response, [
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'roomCaches' => $roomCaches
        ]);
    }

    /**
     * @Route("/save", name="room_cache_overview_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:RoomCache:index.html.twig")
     * @param Request $request
     * @return array
     */
    public function saveAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $helper = $this->get('mbh.helper');
        $validator = $this->get('validator');
        (empty($request->get('updateRoomCaches'))) ? $updateData = [] : $updateData = $request->get('updateRoomCaches');
        (empty($request->get('newRoomCaches'))) ? $newData = [] : $newData = $request->get('newRoomCaches');


        //new
        foreach ($newData as $roomTypeId => $roomTypeArray) {

            $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
            if (!$roomType || $roomType->getHotel() != $hotel) {
                continue;
            }

            foreach ($roomTypeArray as $tariffId => $tariffArray) {

                if ($tariffId) {
                    $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
                    if (!$tariff || $tariff->getHotel() != $hotel) {
                        continue;
                    }
                }

                foreach ($tariffArray as $date => $totalRooms) {
                    if (trim($totalRooms['rooms']) === '' || $totalRooms['rooms'] === null) {
                        continue;
                    }

                    $newRoomCache = new RoomCache();
                    $newRoomCache->setHotel($hotel)
                        ->setRoomType($roomType)
                        ->setDate($helper->getDateFromString($date))
                        ->setTotalRooms((int) $totalRooms['rooms'])
                        ->setPackagesCount(0)
                    ;
                    if ($tariffId && $tariff) {
                        $newRoomCache->setTariff($tariff);
                    }

                    if ($validator->validate($newRoomCache)) {
                        $dm->persist($newRoomCache);
                    }
                }
            }
        }
        $dm->flush();

        //update
        foreach ($updateData as $roomCacheId => $val) {
            $roomCache = $dm->getRepository('MBHPriceBundle:RoomCache')->find($roomCacheId);
            if (!$roomCache || $roomCache->getHotel() != $hotel) {
                continue;
            }

            //delete
            if (isset($val['rooms']) && (trim($val['rooms']) === '' || $val['rooms'] === null)) {
                $dm->remove($roomCache);
                continue;
            }

            if (isset($val['rooms'])) {
                $roomCache->setTotalRooms((int) $val['rooms']);
            }
            $roomCache->setIsClosed(isset($val['closed']) && !empty($val['closed']) ? true : false);
            if ($validator->validate($roomCache)) {
                $dm->persist($roomCache);
            }
        }
        $dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', 'Изменения успешно сохранены.')
        ;

        $this->get('mbh.channelmanager')->updateRoomsInBackground();

        return $this->redirect($this->generateUrl('room_cache_overview', [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
        ]));
    }

    /**
     * @Route("/generator", name="room_cache_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function generatorAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(
            new RoomCacheGeneratorType(), [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/generator/save", name="room_cache_generator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:RoomCache:generator.html.twig")
     * @param Request $request
     * @return array
     */
    public function generatorSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(
            new RoomCacheGeneratorType(), [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            $request->getSession()->getFlashBag()
                ->set('success', 'Данные успешно сгенерированы.')
            ;

            $data = $form->getData();

            $this->get('mbh.room.cache')->update(
                $data['begin'], $data['end'], $hotel, $data['rooms'], false,  $data['roomTypes']->toArray(), $data['tariffs']->toArray(), $data['weekdays']
            );

            $this->get('mbh.channelmanager')->updateRoomsInBackground();

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('room_cache_generator'));
            }
            return $this->redirect($this->generateUrl('room_cache_overview'));
        }

        return [
            'form' => $form->createView()
        ];
    }
}
