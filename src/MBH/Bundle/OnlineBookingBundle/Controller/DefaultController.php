<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Form\ReservationType;
use MBH\Bundle\OnlineBookingBundle\Form\SearchFormType;
use MBH\Bundle\OnlineBookingBundle\Form\SignType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineNotifyRecipient;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Lib\PaymentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    /*const RECAPCHA_SECRET = '6Lcj9gcUAAAAAH_zLNfIhoNHvbMRibwDl3d3Thx9';*/

    /**
     * @Route("/", name="online_booking")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $step = $request->get('step');

        if ($step && $step == 2) {
            return $this->signAction($request);//$this->forward('MBHOnlineBookingBundle:Default:sign');
        }

        return $this->searchAction($request);//$this->forward('MBHOnlineBookingBundle:Default:search');
    }

    /**
     * @Route("/form", name="online_booking_form")
     * @Template()
     * @param Request $request
     * @return array
     * @Cache(expires="tomorrow", public=true)
     */
    public function formAction(Request $request)
    {

        $form = $this->createForm(SearchFormType::class);
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];
        $payOnlineUrl = $this->getParameter('online_booking')['payonlineurl'];
        $form->handleRequest($request);

        return [
            'form' => $form->createView(),
            'requestSearchUrl' => $requestSearchUrl,
            'payOnlineUrl' => $payOnlineUrl,
            'restrictions' => json_encode($this->dm->getRepository('MBHPriceBundle:Restriction')->fetchInOut()),
        ];

    }


    /**
     * @Route("/search", name="online_booking_search")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $searchQuery = new SearchQuery();

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        $searchResults = [];

        if ($form->isValid()) {
            $formData = $form->getData();
            if ($formData['roomType']) {
                $searchQuery->addRoomType($formData['roomType']);
            } elseif ($formData['hotel']) {
                if ($this->get('mbh.hotel.room_type_manager')->useCategories) {
                    foreach ($formData['hotel']->getRoomTypesCategories() as $cat) {
                        $searchQuery->addRoomType($cat->getId());
                    }
                } else {
                    $searchQuery->addHotel($formData['hotel']);
                }
            }

            $searchQuery->begin = $formData['begin'];
            $searchQuery->end = $formData['end'];
            $searchQuery->adults = (int)$formData['adults'];
            $searchQuery->children = (int)$formData['children'];

            $searchQuery->isOnline = true;

            $searchQuery->accommodations = true;
            $searchQuery->forceRoomTypes = false;
//            $searchQuery->range = 2;

            if ($formData['children_age']) {
                $searchQuery->setChildrenAges($formData['children_age']);
            };

            $searchResults = $this->get('mbh.package.search')
                ->setAdditionalDates()
                ->setWithTariffs()
                ->search($searchQuery);
            ///////TODO: Убирать тут
//            $searchResults = [];

            foreach ($searchResults as $k => $item) {
                $filterSearchResults = [];
                /** @var SearchResult[] $results */
                $results = $item['results'];
                foreach ($results as $i => $searchResult) {
                    if ($searchResult->getRoomType()->getCategory()) {
                        $uniqid = $searchResult->getRoomType()->getCategory()->getId().$searchResult->getTariff(
                            )->getId();
                        $uniqid .= $searchResult->getBegin()->format('dmY').$searchResult->getEnd()->format('dmY');
                        if (!array_key_exists($uniqid, $filterSearchResults) || $searchResult->getRoomType(
                            )->getTotalPlaces() < $filterSearchResults[$uniqid]->getRoomType()->getTotalPlaces()
                        ) {
                            $filterSearchResults[$uniqid] = $searchResult;
                        }
                    }
                }
                $searchResults[$k]['results'] = $filterSearchResults;
            }
        }

        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        return $this->render(
            'MBHOnlineBookingBundle:Default:search.html.twig',
            [
                'searchResults' => $searchResults,
                'requestSearchUrl' => $requestSearchUrl,
            ]
        );
    }


    /**
     * @Route("/success", name="online_booking_success")
     * @Template()
     */
    public function lastStepAction(Request $request)
    {
        $type = '';
        $payButtonHtml = '';
        $orderId = $request->get('order');
        $cash = $request->get('cash');
        if (!$orderId || !$cash) {
            $type = 'reservation';
        } else {
            $type = 'online';
            $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
            if ($orderId && $cash && $clientConfig->getPaymentSystem()) {
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(['id' => $orderId]);
                if ($order) {
                    $payButtonHtml = $this->renderView(
                        'MBHClientBundle:PaymentSystem:'.$clientConfig->getPaymentSystem().'.html.twig',
                        [
                            'data' => array_merge(
                                [
                                    'test' => false,
                                    'buttonText' => $this->get('translator')->trans(
                                        'views.api.make_payment_for_order_id',
                                        ['%total%' => number_format($cash, 2), '%order_id%' => $order->getId()],
                                        'MBHOnlineBundle'
                                    ),
                                ],
                                $clientConfig->getFormData(
                                    $order->getCashDocuments()[0],
                                    $this->container->getParameter('online_form_result_url'),
                                    $this->generateUrl('online_form_check_order', [], true)
                                )
                            ),
                        ]
                    );
                }
            }
        }

        return [
            'type' => $type,
            'order' => $orderId,
            'payButtonHtml' => $payButtonHtml,
        ];
    }

    /**
     * @Route("/sign", name="online_booking_sign")
     */
    public function signAction(Request $request)
    {
        $reservation = $request->get('reservation');
        if ($reservation) {
            $form = $this->createForm(ReservationType::class);
        } else {
            ///////////////
            $form = $this->createForm(SignType::class);
            ///////////////
        }
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        $isSubmit = $request->get('submit');

        if ($isSubmit) {
            $form->submit($request->get('form'));
        } else {
            $form->setData($request->get('form'));
        }
        if ($isSubmit && $form->isValid()) {
            $helper = $this->get('mbh.helper');
            $orderManger = $this->get('mbh.order_manager');
            $formData = $form->getData();
            $packages = [
                [
                    'begin' => $helper->getDateFromString($formData['begin']),
                    'end' => $helper->getDateFromString($formData['end']),
                    'adults' => $formData['adults'],
                    'children' => $formData['children'],
                    'roomType' => $formData['roomType'],
                    'tariff' => $formData['tariff'],
                    'accommodation' => false,
                    'isOnline' => true,
                ],
            ];
            $tourist = [
                'firstName' => $formData['firstName'],
                'lastName' => $formData['lastName'],
                'email' => $formData['email'],
                'phone' => $formData['phone'],
                'birthday' => null,
            ];
            $data = [
                'packages' => $packages,
                'tourist' => $tourist,
                'status' => 'online',
                'confirmed' => false,
            ];
            //--> Если по телефону - сюда
            if ($reservation) {
                $data['total'] = $formData['total']??0;
                $this->reserveNotification($data);

                return $this->render('@MBHOnlineBooking/Default/sign-success.html.twig');
            }
            //Price online pay
            $paymentType = PaymentType::PAYMENT_TYPE_LIST[$formData['paymentType']];
            //OnlinePayment  - оплаченая цена (формируется взависимости от выбора. Искать в форме)
            $onlinePaymentSum = (int)$formData['total'] / 100 * $paymentType['value'];
            $cash = ['total' => $onlinePaymentSum];
            $data['onlinePaymentType'] = 'online_full';

            //Создаем бронь.
            try {
                $order = $orderManger->createPackages($data, null, null, $cash);
            } catch (Exception $e) {
                $text = 'Произошла ошибка при бронировании, пожалуйста, позвоните нам.';

                return $this->render(
                    'MBHOnlineBookingBundle:Default:sign-false.html.twig',
                    [
                        'text' => $text,
                    ]
                );
            }

//            $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
            $arrival = $this->getParameter('mbh.package.arrival.time');
            $departure = $this->getParameter('mbh.package.departure.time');
            $this->sendNotifications($order, $arrival, $departure);

            //--> Сюда если онлайн.
            return $this->render(
                'MBHOnlineBookingBundle:Default:sign-success.html.twig',
                [
                    'order' => $order->getId(),
                    'cash' => $cash['total'],
                ]
            );
        } else {
            $data = $isSubmit ? $form->getData() : $request->get('form');
            $roomTypeID = $data['roomType'];
            $tariffID = $data['tariff'];

            /** @var RoomType $roomType */
            $roomType = $this->dm->getRepository(RoomType::class)->find($roomTypeID);
            if (!$roomType) {
                throw $this->createNotFoundException('Room type is not exists');
            }

            $roomTypeCategory = null;
            if ($this->get('mbh.hotel.room_type_manager')->useCategories) {
                $roomTypeCategory = $roomType->getCategory();
            }

            /** @var Tariff $tariff */
            $tariff = $this->dm->getRepository(Tariff::class)->find($tariffID);
            if (!$tariff) {
                throw $this->createNotFoundException('Tariff is not exists');
            }

            $beginTime = $this->get('mbh.helper')->getDateFromString($data['begin']);
            $endTime = $this->get('mbh.helper')->getDateFromString($data['end']);

            $days = 1;
            if ($beginTime && $endTime) {
                $days = $endTime->diff($beginTime)->d;
            }

            return $this->render(
                'MBHOnlineBookingBundle:Default:sign.html.twig',
                [
                    'requestSearchUrl' => $requestSearchUrl,
                    'form' => $form->createView(),
                    'roomType' => $roomType,
                    'roomTypeCategory' => $roomTypeCategory,
                    'tariff' => $tariff,
                    'data' => $data,
                    'days' => $days,
                    'offera' => $this->getParameter('offera'),
                ]
            );
        }
    }


    /**
     * @param Order $order
     * @param string $arrival
     * @param string $departure
     * @return bool
     */
    private function sendNotifications(Order $order, $arrival = '12:00', $departure = '12:00')
    {
        try {
            //backend
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->get('translator');
            $message = $notifier::createMessage();
            $hotel = $order->getPackages()[0]->getRoomType()->getHotel();
            $message
                ->setText($tr->trans('mailer.online.backend.text', ['%orderID%', $order->getId()]))
                ->setFrom('online_form')
                ->setSubject('mailer.online.backend.subject')
                ->setType('info')
                ->setCategory('notification')
                ->setOrder($order)
                ->setAdditionalData(
                    [
                        'arrivalTime' => $arrival,
                        'departureTime' => $departure,
                    ]
                )
                ->setHotel($hotel)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'));
            $notifier
                ->setMessage($message)
                ->notify();

            //user
            $payer = $order->getPayer();
            if ($payer && $payer->getEmail()) {
                $notifier = $this->container->get('mbh.notifier.mailer');
                $message = $notifier::createMessage();
                $message
                    ->setFrom('online_form')
                    ->setSubject('mailer.online.user.subject')
                    ->setType('info')
                    ->setCategory('notification')
                    ->setOrder($order)
                    ->setAdditionalData(
                        [
                            'prependText' => 'mailer.online.user.prepend',
                            'appendText' => 'mailer.online.user.append',
                            'fromText' => $hotel->getName(),
                        ]
                    )
                    ->setHotel($hotel)
                    ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($payer)
                    ->setLink('hide')
                    ->setSignature('mailer.online.user.signature');

                $params = $this->container->getParameter('mailer_user_arrival_links');

                if (!empty($params['map'])) {
                    $message->setLink($params['map'])
                        ->setLinkText($tr->trans('mailer.online.user.map'));
                }

                $notifier
                    ->setMessage($message)
                    ->notify();
            }

        } catch (\Exception $e) {

            return false;
        }
    }

    /**
     * @param $data
     */
    private function reserveNotification($data)
    {
        $notifier = $this->container->get('mbh.notifier');
        $message = $notifier::createMessage();
        $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
            ['id' => $data['packages'][0]['roomType']]
        );
        $hotel = $roomType->getHotel();
        $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(
            ['id' => $data['packages'][0]['tariff']]
        );
        $recipient = new OnlineNotifyRecipient();
        $recipient->setEmail($this->container->getParameter('online_reservation_manager_email'));
        $message
            ->setRecipients([$recipient])
            ->setSubject('mailer.online.backend.reservation.subject')
            ->setText('mailer.online.backend.reservation.text')
            ->setFrom('online_form')
            ->setType('info')
            ->setCategory('notification')
            ->setAdditionalData(
                [
                    'roomType' => $roomType,
                    'tariff' => $tariff,
                    'begin' => $data['packages'][0]['begin'],
                    'end' => $data['packages'][0]['end'],
                    'client' => $data['tourist'],
                    'total' => $data['total'],
                    'package' => $data['packages'][0],

                ]
            )
            ->setHotel($hotel)
            ->setTemplate('MBHOnlineBookingBundle:Mailer:reservation.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $notifier
            ->setMessage($message)
            ->notify();

        if ($data['tourist']['email']) {
            $tourist = $data['tourist'];
            $notifier = $this->container->get('mbh.notifier.mailer');
            $recipient = new OnlineNotifyRecipient();
            $recipient
                ->setName($tourist['firstName'].' '.$tourist['lastName'])
                ->setEmail($tourist['email']);
            $message
                ->setRecipients([$recipient])
                ->setSubject('mailer.online.backend.reservation.client.subject')
                ->setText('mailer.online.backend.reservation.client.text')
                ->setTemplate('MBHOnlineBookingBundle:Mailer:reservation.client.html.twig');
            $notifier
                ->setMessage($message)
                ->notify();
        }
    }

    /**
     * @Route("/minstay/{timestamp}", name="online_booking_min_stay", options={"expose" = true})
     * @Cache(expires="tomorrow", public=true)
     */
    public function getMinStayAjax($timestamp)
    {

        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        $minStays = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetchMinStay($date);
        $data = [
            'success' => true,
            'minstay' => $minStays,
        ];

        $env = $this->get('kernel')->getEnvironment();
        $response = new JsonResponse(json_encode($data));
        $response->headers->set('Access-Control-Allow-Origin', $this->getParameter('domain_access_control_'.$env));

        return $response;
    }

    /**
     * @Route(
     *     "/calculation/{tariffId}/{roomTypeId}/{adults}/{children}/{packageBegin}/{packageEnd}",
     *      name="online_booking_calculation",
     *      options={"expose" = true}
     *     )
     * @ParamConverter("tariff", class="MBHPriceBundle:Tariff", options={"id" = "tariffId"})
     * @ParamConverter("roomType", class="MBHHotelBundle:RoomType", options={"id" = "roomTypeId"})
     * @param Request $request
     * @param Tariff $tariff
     * @param RoomType $roomType
     * @param int $adults
     * @param int $children
     * @param \DateTime $packageBegin
     * @param \DateTime $packageEnd
     * @return JsonResponse
     */
    public function getCalculate(
        Request $request,
        Tariff $tariff,
        RoomType $roomType,
        int $adults,
        int $children,
        \DateTime $packageBegin = null,
        \DateTime $packageEnd = null
    ) {

        $former = $this->get('mbh.online.chart.data.former');
        $data = $former->getPriceCalendarData($roomType, $tariff, $adults, $children, $packageBegin, $packageEnd);

        $response = new JsonResponse($data);
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));

        return $response;
    }

}
