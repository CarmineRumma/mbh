<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Form\HomeAwayType;
use MBH\Bundle\ChannelManagerBundle\Form\RoomsType;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController;

/**
 * @Route("/homeaway")
 */
class HomeAwayController extends BaseController
{
    /**
     * @Route("/", name="homeaway")
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        if (!$config) {
            $config = new HomeAwayConfig();
            $config->setHotel($this->hotel);
        }

        $form = $this->createForm(HomeAwayType::class, $config, [
            'hotel' => $this->hotel
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($config);
            $this->dm->flush();
            $this->hotel->setHomeAwayConfig($config);

            $this->addFlash('success',
                $this->get('translator')->trans('controller.homeAwayController.settings_saved_success'));
        }

        return [
            'doc' => $this->hotel->getHomeAwayConfig(),
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * Room configuration page
     * @Route("/room", name="homeaway_room")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_HOMEAWAY')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function roomAction(Request $request)
    {
        $config = $this->hotel->getHomeAwayConfig();

        if (!$config) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomsType::class, $config->getRoomsAsArray(), [
            'hotel' => $this->hotel,
            //TODO: получать комнаты
//            'booking' => $this->get('mbh.channelmanager.homeaway_data_formatter')->pullRooms($config),
        ]);

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

            $this->addFlash('success',
                $this->get('translator')->trans('controller.homeAwayController.settings_saved_success'));

            return $this->redirect($this->generateUrl('homeaway_room'));
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }

    /**
     * @Route("/rates/{listingId}", name="homeaway_rates")
     * @param Request $request
     * @return Response
     */
    public function ratesAction(Request $request)
    {
//        $this->get('mbh.channelmanager.homeaway_data_formatter')->formatListingContentIndex()
        return new Response();
    }

    /**
     * @Route("/routes/{listingId}", name="homeaway_availability")
     * @param $listingId
     * @return Response
     */
    public function availabilityAction($listingId)
    {
        $this->get('mbh.channelmanager.homeaway_data_formatter')->formatAvailabilityData($listingId);

        return new Response();
    }

    public function quoteRequestAction(Request $request)
    {

    }

    public function bookingRequestAction(Request $request)
    {
        //TODO: Поменять название
        $bookingRequest = $request->get('xml');
        $bookingRequestXML = new \SimpleXMLElement($bookingRequest);
        $documentVersion = (string)$bookingRequestXML->documentVersion;
        $bookingRequestDetails = $bookingRequestXML->bookingRequestDetails[0];
        $config = $this->hotel->getHomeAwayConfig();
        $orderInfo = $this->get('mbh.channelmanager.homeaway_order_info')->setInitData($bookingRequestDetails, $config);
        $resultOfCreation = $this->get('mbh.channel_manager.order_handler')->createOrder($orderInfo);
        $bookingCreationResponse = $this->get('mbh.channelmanager.homeaway_data_formatter')
            ->getBookingResponse($documentVersion, $orderInfo, $orderInfo->getMessages());


    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
        $xml = '<inquirer>
            <title>Ms.</title>
            <firstName>Amy</firstName>
            <lastName>Smith</lastName>
            <emailAddress>amy@gmail.com</emailAddress>
            <phoneNumber> 5125551212</phoneNumber>
            <address rel="BILLING">
                <addressLine1>10 Main Street</addressLine1>
                <addressLine3>Austin</addressLine3>
                <addressLine4>TX</addressLine4>
                <country>US</country>
                <postalCode>78703</postalCode>
            </address>
        </inquirer>';

        $xmlElement = new \SimpleXMLElement($xml);

        return new Response(true ? 'true' : 'false');
    }
}