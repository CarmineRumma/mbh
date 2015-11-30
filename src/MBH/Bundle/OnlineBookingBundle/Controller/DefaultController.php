<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    public function getSearchForm()
    {
        /** @var RoomTypeRepositoryInterface $roomTypeRepository */
        $roomTypeRepository = $this->get('mbh.hotel.room_type_manager')->getRepository();
        $roomTypes = $roomTypeRepository->findAll();

        $roomTypeList = [];
        $hotelIds = [];
        foreach($roomTypes as $roomType) {
            $hotelIds[$roomType->getId()] = $roomType->getHotel()->getId();
            $roomTypeList[$roomType->getId()] = $roomType->__toString();
        }

        return $this->createFormBuilder([], [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false
        ])
            ->add('hotel', 'document', [
                'empty_value' => '',
                'class' => Hotel::class
            ])
            ->add('roomType', 'choice', [
                'empty_value' => '',
                'choices' => $roomTypeList,
                'choice_attr' => function($roomType) use($hotelIds) {
                    return ['data-hotel' => $hotelIds[$roomType]];
                }
            ])
            ->add('begin', new DateType(), [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add('end', new DateType(), [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add('adults', 'integer', [])
            ->add('children', 'integer', [
                'attr' => ['min' => 1, 'max' => 10],
                'required' => false
            ])
            ->add('children_age', 'collection', [
                'required' => false,
                'type' => 'integer',
                'prototype' => true,
                'allow_add' => true,
            ])
            ->getForm()
        ;
    }

    /**
     * @Route("/form", name="online_booking_form")
     */
    public function formAction(Request $request)
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        $form = $this->getSearchForm();
        $form->handleRequest($request);

        return $this->render('MBHOnlineBookingBundle:Default:form.html.twig', [
            'hotels' => $hotels,
            'requestSearchUrl' => $requestSearchUrl,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/", name="online_booking")
     */
    public function indexAction(Request $request)
    {
        $step = $request->get('step');
        if($step) {
            if($step == 2) {
                return $this->signAction($request);//$this->forward('MBHOnlineBookingBundle:Default:sign');
            }
        } else {
            return $this->searchAction($request);//$this->forward('MBHOnlineBookingBundle:Default:search');
        }
    }

    /**
     * @Route("/search", name="online_booking_search")
     */
    public function searchAction(Request $request)
    {
        $searchQuery = new SearchQuery();

        $form = $this->getSearchForm();
        $form->handleRequest($request);

        $searchResults = [];
        if($form->isValid()) {
            $formData = $form->getData();
            if($formData['hotel']) {
                $searchQuery->addHotel($formData['hotel']);
            }
            if($formData['roomType']) {
                $searchQuery->addRoomType($formData['roomType']);
                /*$roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($formData['roomType']);
                if($roomType) {
                    $searchQuery->addRoomType($roomType->getId());
                }
                $category = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory')->find($formData['roomType']);
                if($category) {
                    foreach($category->getRoomTypes() as $roomType) {
                        $searchQuery->addRoomType($roomType->getId());
                    }
                }*/
            }
            $searchQuery->begin = $formData['begin'];
            $searchQuery->end = $formData['end'];
            $searchQuery->adults = (int)$formData['adults'];
            $searchQuery->children = (int)$formData['children'];
            $searchQuery->accommodations = true;
            $searchQuery->isOnline = true;
            if($formData['children_age']) {
                $searchQuery->setChildrenAges($formData['children_age']);
            };

            $searchResults = $this->get('mbh.package.search')
                ->setAdditionalDates()
                ->setWithTariffs()
                ->search($searchQuery)
            ;
        }

        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        return $this->render('MBHOnlineBookingBundle:Default:search.html.twig', [
            'searchResults' => $searchResults,
            'requestSearchUrl' => $requestSearchUrl
        ]);
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getSignForm()
    {
        return $this->createFormBuilder(null, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false
        ])
            ->add('firstName', 'text', [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('lastName', 'text', [
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('patronymic', 'text', [
                'label' => 'Отчество'
            ])
            ->add('phone', 'text', [
                'label' => 'Телефон'
            ])
            ->add('email', 'text', [
                'label' => 'Email'
            ])
            //->add('step', 'hidden', [])
            ->add('adults' , 'hidden', [])
            ->add('children', 'hidden', [])
            ->add('begin', 'hidden', [
                'constraints' => [
                    new NotBlank()
                ]])
            ->add('end', 'hidden', [
                'constraints' => [
                    new NotBlank()
                ]])
            ->add('roomType', 'hidden', [])
            ->add('tariff', 'hidden', [])
            ->getForm()
        ;
    }

    /**
     * @Route("/search", name="online_booking_sign")
     */
    public function signAction(Request $request)
    {
        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];
        $form = $this->getSignForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
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
                    'isOnline' => true
                ]
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
                'confirmed' => false
            ];
            $order = $orderManger->createPackages($data);
            return new Response('Заказ успешно создан №'. $order->getId());
        } else {
            return $this->render('MBHOnlineBookingBundle:Default:sign.html.twig', [
                'requestSearchUrl' => $requestSearchUrl,
                'form' => $form->createView()
            ]);
        }
    }
}
