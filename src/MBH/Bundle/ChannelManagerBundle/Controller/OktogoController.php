<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Form\TariffsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\ChannelManagerBundle\Form\OktogoType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\ChannelManagerBundle\Form\TariffType;

/**
 * @Route("/oktogo")
 */
class OktogoController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="oktogo")
     * @Method("GET")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function indexAction()
    {
        $entity = $this->get('mbh.hotel.selector')->getSelected()->getOktogoConfig();

        $form = $this->createForm(
            new OktogoType(), $entity
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="oktogo_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template("MBHChannelManagerBundle:Oktogo:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();
        $new = false;

        if (!$entity) {
            $entity = new OktogoConfig();
            $entity->setHotel($hotel);
            $new = true;
        }

        $form = $this->createForm(
            new OktogoType(), $entity
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            if ($new) {
                //$this->get('mbh.channelmanager.oktogo')->roomSync($entity);
                //$this->get('mbh.channelmanager.oktogo')->tariffSync($entity);
                //$dm->persist($entity);
                //$dm->flush();
            }

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.oktogoController.settings_saved_success'))
            ;

//            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            return $this->redirect($this->generateUrl('oktogo'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/room", name="oktogo_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function roomAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $config = $this->hotel->getOktogoConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new RoomsType, $config->getRoomsAsArray(), [
                'hotel' => $this->hotel,
                'booking' => $this->get('mbh.channelmanager.oktogo')->pullRooms($config),
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllRooms();
            foreach ($form->getData() as $id => $roomType) {
                if ($roomType) {
                    $configRoom = new Room();
                    $configRoom->setRoomType($roomType)->setRoomId($id);
                    $config->addRoom($configRoom);
                    $this->dm->persist($config);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success'));

            return $this->redirect($this->generateUrl('oktogo_room'));
        }

        return array(
            'config' => $config,
            'logs' => $this->logs($config),
            'form' => $form->createView(),
        );
    }

//    /**
//     * @Route("/room", name="oktogo_room_save")
//     * @Method("POST")
//     * @Security("is_granted('ROLE_OKTOGO')")
//     * @Template("MBHChannelManagerBundle:Oktogo:room.html.twig")
//     */
//    public function roomSaveAction(Request $request)
//    {
//        $hotel = $this->get('mbh.hotel.selector')->getSelected();
//        $entity = $hotel->getOktogoConfig();
//
//        if (!$entity) {
//            throw $this->createNotFoundException();
//        }
//
//        $form = $this->createForm(
//            new RoomTypes(), [], ['entity' => $entity]
//        );
//
//        $form->submit($request);
//
//        if ($form->isValid()) {
//
//            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
//            $dm = $this->get('doctrine_mongodb')->getManager();
//
//            $entity->removeAllRooms();
//
//            foreach ($form->getData() as $roomTypeId => $value) {
//                if ($value === null) {
//                    continue;
//                }
//
//                $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
//
//                if (!$roomType) {
//                    continue;
//                }
//                $room = new Room();
//                $room->setRoomType($roomType)->setRoomId($value);
//                $entity->addRoom($room);
//            }
//            $dm->persist($entity);
//            $dm->flush();
//
//            $request->getSession()->getFlashBag()
//                ->set('success', $this->get('translator')->trans('controller.oktogoController.settings_saved_success'))
//            ;
//            if ($request->get('save') !== null) {
//
//                return $this->redirect($this->generateUrl('oktogo_room'));
//            }
//
//            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
//
//            return $this->redirect($this->generateUrl('oktogo'));
//        }
//
//        return array(
//            'entity' => $entity,
//            'logs' => $this->logs($entity),
//            'form' => $form->createView(),
//        );
//    }

    /**
     * @Route("/room/sync", name="oktogo_room_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function roomSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.oktogo')->roomSync($entity);

        if ($result) {
            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.oktogoController.rooms_sync_success'))
            ;

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
        } else {
            $request->getSession()->getFlashBag()
                ->set('danger', $this->get('translator')->trans('controller.oktogoController.sync_error'))
            ;
        }

        return $this->redirect($this->generateUrl('oktogo_room'));
    }

    /**
     * @Route("/tariff/sync", name="oktogo_tariff_sync")
     * @Method("GET")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function tariffSyncAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $result = $this->get('mbh.channelmanager.oktogo')->tariffSync($entity);

        if ($result) {
            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.oktogoController.tariffs_sync_success'))
            ;

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();
        } else {
            $request->getSession()->getFlashBag()
                ->set('danger', $this->get('translator')->trans('controller.oktogoController.sync_error'))
            ;
        }

        return $this->redirect($this->generateUrl('oktogo_tariff'));
    }

    /**
     * @Route("/tariff", name="oktogo_tariff")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template()
     */
    public function tariffAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $config = $hotel->getOktogoConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $this->get('mbh.channelmanager.oktogo')->updateRooms();

        $form = $this->createForm(new TariffsType(), $config->getTariffsAsArray(), [
            'hotel' => $this->hotel,
            'booking' => $this->get('mbh.channelmanager.oktogo')->pullTariffs($config),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->removeAllTariffs();
            foreach ($form->getData() as $id => $tariff) {
                if ($tariff) {
                    $configTariff = new Tariff();
                    $configTariff->setTariff($tariff)->setTariffId($id);
                    $config->addTariff($configTariff);
                    $this->dm->persist($config);
                }
            }
            $this->dm->flush();

            $this->get('mbh.channelmanager')->updateInBackground();

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.bookingController.settings_saved_success'));

            return $this->redirect($this->generateUrl('oktogo_tariff'));
        }

        return array(
            'config' => $config,
            'logs' => $this->logs($config),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/tariff", name="oktogo_tariff_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_OKTOGO')")
     * @Template("MBHChannelManagerBundle:Oktogo:tariff.html.twig")
     */
    public function tariffSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity = $hotel->getOktogoConfig();

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new TariffType(), [], ['entity' => $entity, 'hideDefault' => false]
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $entity->removeAllTariffs();

            foreach ($form->getData() as $tariffId => $value) {
                if ($value === null) {
                    continue;
                }

                $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);

                if (!$tariff) {
                    continue;
                }

                $oktogoTariff = new Tariff();
                $oktogoTariff->setTariff($tariff)->setTariffId($value);
                $entity->addTariff($oktogoTariff);
            }

            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;

            $this->get('mbh.room.cache.generator')->updateChannelManagerInBackground();

            if ($request->get('save') !== null) {

                return $this->redirect($this->generateUrl('oktogo_tariff'));
            }

            return $this->redirect($this->generateUrl('oktogo'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * Services configuration page
     * @Route("/service", name="oktogo_service")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     */
    public function serviceAction()
    {
        $config = $this->get('mbh.hotel.selector')->getSelected()->getBookingConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        return [
            'doc' => $config,
            'logs' => $this->logs($config)
        ];
    }
}
