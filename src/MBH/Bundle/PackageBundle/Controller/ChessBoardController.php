<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\ChessBoardConciseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\PackageBundle\Form\SearchType;
use Zend\Json\Json;

/**
 * @Route("/chessboard")
 */
class ChessBoardController extends BaseController
{
    /**
     * @Route("/", name="chess_board_home")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $filterData = $this->getFilterData($request);

        $builder = $this->get('mbh.package.report_data_builder')
            ->init($this->hotel, $filterData['begin'], $filterData['end'], $filterData['roomTypeIds']);

        $form = $this->createForm(SearchType::class, null, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'roomManager' => $this->get('mbh.hotel.room_type_manager')
        ]);

        return [
            'searchForm' => $form->createView(),
            'beginDate' => $filterData['begin'],
            'endDate' => $filterData['end'],
            'calendarData' => $builder->getCalendarData(),
            'days' => $builder->getDaysArray(),
            'roomTypesData' => $builder->getRoomTypeData(),
            'leftRoomsData' => $builder->getLeftRoomCounts(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
            'packages' => json_encode($builder->getPackageData()),
            'leftRoomsJsonData' => json_encode($builder->getLeftRoomCounts()),
            'noAccommodationCounts' => json_encode($builder->getDayNoAccommodationPackageCounts()),
            'roomTypes' => $this->hotel->getRoomTypes(),
            'housings' => $this->hotel->getHousings(),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
        ];
    }



    /**
     * @Method({"GET"})
     * @Route("/packages/{id}", name="chessboard_get_package", options={"expose"=true})
     * @param Package $package
     * @Template()
     * @return array
     */
    public function getPackageAction(Package $package)
    {
        return [
            'package' => $package,
            'currency' => $this->getParameter('locale.currency')
        ];
    }

    /**
     * @Method({"DELETE"})
     * @Route("/packages/{id}", name="chessboard_remove_package", options={"expose"=true})
     * @param Package $package
     * @return JsonResponse
     * @internal param Request $request
     */
    public function removePackageAction(Package $package)
    {
        $this->dm->remove($package);
        $this->dm->flush();

        return new JsonResponse(json_encode(
            [
                'success' => true,
                'messages' => [$this->get('translator')->trans('controller.chessboard.package_remove.success')]
            ]
        ));
    }

    /**
     * @Method({"PUT"})
     * @Route("/packages/{id}", name="concise_package_update", options={"expose"=true})
     * @param Request $request
     * @param Package $package
     * @return JsonResponse
     */
    public function updatePackageAction(Request $request, Package $package)
    {
        $helper = $this->container->get('mbh.helper');
        $oldPackage = clone $package;

        $package->setBegin($helper::getDateFromString($request->request->get('begin')));
        $package->setEnd($helper::getDateFromString($request->request->get('end')));
        $package->setRoomType($this->dm->find('MBHHotelBundle:RoomType', $request->request->get('roomType')));
        $package->setAccommodation($this->dm->find('MBHHotelBundle:Room', $request->request->get('room')));

        $errors = $this->get('validator')->validate($package);

        if (count($errors) === 0) {
            $result = $this->container->get('mbh.order_manager')->updatePackage($oldPackage, $package);
            if ($result instanceof Package) {
                $this->dm->persist($package);
                $this->dm->flush();
                $response = [
                    'success' => true,
                    'messages' => [$this->get('translator')->trans('controller.chessboard.package_update.success')]
                ];
            } else {
                $response = [
                    'success' => false,
                    'messages' => [$this->get('translator')->trans($result)]
                ];
            }
        } else {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            $response = [
                'success' => false,
                'messages' => $errorMessages
            ];
        }

        return new JsonResponse(json_encode($response));
    }


    /**
     * @Method({"GET"})
     * @Route("/packages", name="chessboard_packages", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPackagesData(Request $request)
    {
        $packageData = $this->getChessBoardDataByFilters($request);

        return new JsonResponse($packageData);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getChessBoardDataByFilters(Request $request)
    {
        $filterData = $this->getFilterData($request);
        $builder = $this->get('mbh.package.report_data_builder')
            ->init($this->hotel, $filterData['begin'], $filterData['end'], $filterData['roomTypeIds']);

        $data['packages'] = $builder->getPackageData();
        $data['leftRoomCounts'] = $builder->getLeftRoomCounts();
        $data['noAccommodationCounts'] = $builder->getDayNoAccommodationPackageCounts();

        return json_encode($data);
    }

    private function getFilterData(Request $request)
    {
        $helper = $this->container->get('mbh.helper');
        $beginDate = $helper->getDateFromString($request->get('filter_begin'));
        if (!$beginDate) {
            $beginDate = new \DateTime('00:00');
            $beginDate->modify('-5 days');
        }
        $endDate = $helper->getDateFromString($request->get('filter_end'));
        if (!$endDate || $endDate->diff($beginDate)->format("%a") > 160 || $endDate <= $beginDate) {
            $endDate = (clone $beginDate)->add(new \DateInterval('P20D'));
        }

        $roomTypeIds = [];
        $roomTypes = $request->get('filter_roomType');
        if (!empty($roomTypes)) {
            if (is_array($roomTypes)) {
                if ($roomTypes[0] != "") {
                    $roomTypeIds = $roomTypes;
                }
            } else {
                $roomTypeIds[] = $roomTypes;
            }
        }
        $housing = $request->get('housing');
        $floor = $request->get('floor');

        return [
            'begin' => $beginDate,
            'end' => $endDate,
            'roomTypeIds' => $roomTypeIds,
            'housing' => $housing,
            'floor' => $floor
        ];
    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
    }
}