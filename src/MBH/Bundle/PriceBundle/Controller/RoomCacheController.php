<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Form\RoomCacheGeneratorType;
use MBH\Bundle\PriceBundle\Lib\RoomCacheGraphGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("room_cache")
 */
class RoomCacheController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="room_cache_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $isDisableableOn = $this->dm->getRepository('MBHClientBundle:ClientConfig')->isDisableableOn();
        //get roomTypes
        $roomTypesCallback = function () use ($hotel) {
            return $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $hotel->getId()]);
        };
        $roomTypes = $this->helper->getFilteredResult($this->dm, $roomTypesCallback, $isDisableableOn);

        return [
            'roomTypes' => $roomTypes,
            'tariffs' => $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'rooms'),
            'displayDisabledRoomType' => !$isDisableableOn
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/graph", name="room_cache_overview_graph", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_VIEW')")
     * @Template()
     */
    public function graphAction(Request $request)
    {

        $generator = $this->get('mbh.room.cache.graph.generator');

        return [
            'data' => $generator->generate($request, $this->hotel),
            'begin' => $generator->getBegin(),
            'end' => $generator->getEnd(),
            'error' => $generator->getError()
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/table", name="room_cache_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_VIEW')")
     * @Template()
     */
    public function tableAction(Request $request)
    {
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
        $roomTypesCallback = function () use ($hotel, $request) {
            return $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel, $request->get('roomTypes'));
        };
        $isDisableableOn = $this->dm->getRepository('MBHClientBundle:ClientConfig')->isDisableableOn();
        $roomTypes = $helper->getFilteredResult($this->dm, $roomTypesCallback, $isDisableableOn);

        if (!count($roomTypes)) {
            return array_merge($response, ['error' => $this->container->get('translator')->trans('price.tariffcontroller.room_type_is_not_found')]);
        }

        //get tariffs
        if (!empty($request->get('tariffs'))) {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->fetchChildTariffs($hotel, 'rooms', $request->get('tariffs'))
            ;
        } else {
            $tariffs = [null];
        }

        //get roomCaches
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
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
     * @Security("is_granted('ROLE_ROOM_CACHE_EDIT')")
     * @Template("MBHPriceBundle:RoomCache:index.html.twig")
     * @param Request $request
     * @return array
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $helper = $this->get('mbh.helper');
        $validator = $this->get('validator');
        (empty($request->get('updateRoomCaches'))) ? $updateData = [] : $updateData = $request->get('updateRoomCaches');
        (empty($request->get('newRoomCaches'))) ? $newData = [] : $newData = $request->get('newRoomCaches');
        $availableTariffs = $this->helper->toIds(
            $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'rooms')
        );

        //new
        foreach ($newData as $roomTypeId => $roomTypeArray) {

            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
            if (!$roomType || $roomType->getHotel() != $hotel) {
                continue;
            }

            foreach ($roomTypeArray as $tariffId => $tariffArray) {

                if ($tariffId) {
                    if (!in_array($tariffId, $availableTariffs)) {
                        continue;
                    }

                    $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
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
                        $this->dm->persist($newRoomCache);
                    }
                }
            }
        }
        $this->dm->flush();

        //update
        foreach ($updateData as $roomCacheId => $val) {
            $roomCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')->find($roomCacheId);
            if (!$roomCache || $roomCache->getHotel() != $hotel) {
                continue;
            }

            //delete
            if (isset($val['rooms']) && (trim($val['rooms']) === '' || $val['rooms'] === null)) {
                if ($roomCache->getPackagesCount() <= 0) {
                    $this->dm->remove($roomCache);
                }
                continue;
            }

            if (isset($val['rooms'])) {
                $roomCache->setTotalRooms((int) $val['rooms']);
            }
            $roomCache->setIsClosed(isset($val['closed']) && !empty($val['closed']) ? true : false);
            if ($validator->validate($roomCache)) {
                $this->dm->persist($roomCache);
            }
        }
        $this->dm->flush();

        $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('price.tariffcontroller.update_successfully_saved'));
        $this->get('mbh.channelmanager')->updateRoomsInBackground();
        $this->get('mbh.cache')->clear('room_cache');

        return $this->redirectToRoute('room_cache_overview', [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
        ]);
    }

    /**
     * @Route("/generator", name="room_cache_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_EDIT')")
     * @Template()
     */
    public function generatorAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(RoomCacheGeneratorType::class, [], [
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
     * @Security("is_granted('ROLE_ROOM_CACHE_EDIT')")
     * @Template("MBHPriceBundle:RoomCache:generator.html.twig")
     * @param Request $request
     * @return array
     */
    public function generatorSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(RoomCacheGeneratorType::class, [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $request->getSession()->getFlashBag()->set('success', $this->container->get('translator')->trans('price.tariffcontroller.data_successfully_generated'));

            $data = $form->getData();

            $this->get('mbh.room.cache')->update(
                $data['begin'], $data['end'], $hotel, $data['rooms'], false,  $data['roomTypes']->toArray(), $data['tariffs']->toArray(), $data['weekdays']
            );

            $this->get('mbh.channelmanager')->updateRoomsInBackground();
            $this->get('mbh.cache')->clear('room_cache');

            return $this->isSavedRequest() ?
                $this->redirectToRoute('room_cache_generator') :
                $this->redirectToRoute('room_cache_overview');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
