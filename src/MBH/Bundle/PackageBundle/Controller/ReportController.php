<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/report")
 */
class ReportController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Accommodation report.
     *
     * @Route("/accommodation/index", name="report_accommodation")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function accommodationAction()
    {
        var_dump($this->dm->getRepository('MBHHotelBundle:Room')->fetchHousings());

        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes()
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/accommodation/table", name="report_accommodation_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function accommodationTableAction(Request $request)
    {
        /* @var $this->dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $this->dm = $this->get('doctrine_mongodb')->getManager();

        //get dates
        $begin = new \DateTime();
        if (!empty($request->get('begin'))) {
            $begin = \DateTime::createFromFormat('d.m.Y', $request->get('begin'));
            if (!$begin) {
                $begin = new \DateTime();
            }
        }
        $begin->setTime(0, 0, 0);
        $from = clone $begin;
        $to = clone $begin;
        $from->modify('-18 day');
        $to->modify('+18 days');
        $period = new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to);

        //paging
        (!empty($request->get('page'))) ? $page = (int)$request->get('page') : $page = 1;
        $skip = 0;
        $limit = $this->container->getParameter('mbh.reports.accommodation.rooms.max');
        if ($page > 1) {
            $skip = ($page - 1) * $limit;
        }
        $qb = $this->dm->getRepository('MBHHotelBundle:Room')->fetchQuery(
            $this->get('mbh.hotel.selector')->getSelected(),
            $request->get('roomType')
        );
        $total = $qb->getQuery()->count();
        $pages = ceil($total / $limit);

        //getRooms
        $qb = $this->dm->getRepository('MBHHotelBundle:Room')->fetchQuery(
            $this->get('mbh.hotel.selector')->getSelected(),
            $request->get('roomType'),
            $skip,
            $limit
        );
        
        $rooms = $qb->getQuery()->execute();

        //packages
        $roomIds = [];
        foreach ($rooms as $room) {
            $roomIds[] = $room->getId();
        }
        $packages = [];
        if (count($roomIds)) {
            $qb = $this->dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');

            $qb->field('accommodation.id')->in($roomIds)
                ->addOr($qb->expr()->field('begin')->range($from, $to))
                ->addOr($qb->expr()->field('end')->range($from, $to))
                ->addOr(
                    $qb->expr()
                        ->field('end')->gte($to)
                        ->field('begin')->lte($from)
                )
                ->sort('begin', 'asc')
            ;
            $packagesDocs = $qb->getQuery()->execute();

            foreach ($packagesDocs as $package) {
                $packages[$package->getAccommodation()->getId()][] = $package;
            }
        }

        return [
            'begin' => $begin,
            'from' => $from,
            'to' => $to,
            'period' => iterator_to_array($period),
            'currentPage' => $page,
            'total' => $total,
            'pages' => $pages,
            'rooms' => $rooms,
            'packages' => $packages,
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }
}
