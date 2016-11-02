<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ServiceController
 * @package MBH\Bundle\PackageBundle\Controller
 *

 */
class ServiceController extends BaseController
{
    /**
     * @Route("/service/index", name="service_list")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICES_REPORT')")
     * @Template()
     */
    public function indexAction()
    {
        $services = $categories = [];

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findBy(['isEnabled' => true], ['fullTitle' => 'asc', 'title' => 'asc']) as $hotel) {

            $serviceCats = $this->dm
                ->getRepository('MBHPriceBundle:ServiceCategory')
                ->findBy(['hotel.id' => $hotel->getId(), 'isEnabled' => true], ['fullTitle' => 'asc', 'title' => 'asc']);

            foreach ($serviceCats as $category) {
                $categories[(string)$hotel][$category->getId()] = (string)$category;

                $serviceDocs = $serviceCats = $this->dm
                    ->getRepository('MBHPriceBundle:Service')
                    ->findBy(['category.id' => $category->getId(), 'isEnabled' => true], ['fullTitle' => 'asc', 'title' => 'asc']);

                foreach ($serviceDocs as $serviceDoc) {
                    $services[$serviceDoc->getId()] = $serviceDoc;
                }
            }
        }

        return [
            'services' => $services,
            'categories' => $categories
        ];
    }

    /**
     * @Route("/service/ajax", name="ajax_service_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICES_REPORT')")
     * @Template()
     */
    public function ajaxListAction(Request $request)
    {
        $begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $end = $this->get('mbh.helper')->getDateFromString($request->get('end'));
        $service = $request->get('service');
        $category = $request->get('category');
        $services = null;

        //cat services
        if (!empty($category) && empty($service)) {
            $serviceDocs = $serviceCats = $this->dm
                ->getRepository('MBHPriceBundle:Service')
                ->findBy(['category.id' => $category, 'isEnabled' => true]);

            $services = $this->get('mbh.helper')->toIds($serviceDocs);

        } else if (!empty($service)) {
            $services = [$service];
        }

        if (!$begin) {
            $begin = new \DateTime('midnight -7 days');
        }

        if (!$end) {
            $end = new \DateTime('midnight +1 day');
        }

        /** @var DocumentRepository $repository */
        $repository = $this->dm->getRepository('MBHPackageBundle:PackageService');

        $queryBuilder = $repository->createQueryBuilder();
        $queryBuilder->addNor($queryBuilder->expr()
            ->addOr($queryBuilder->expr()
                ->field('begin')->gt($begin)->addAnd($queryBuilder->expr()->field('begin')->gt($end))
            )->addOr($queryBuilder->expr()
                ->field('end')->lt($begin)->addAnd($queryBuilder->expr()->field('end')->lt($end))
            )
        );

        $tableParams = ClientDataTableParams::createFromRequest($request);
        $tableParams->setSortColumnFields([
            //1 => 'package.id',
            2 => 'begin',
            4 => 'nights',
            5 => 'persons',
            6 => 'amount',
            8 => 'total',
        ]);

        if ($firstSort = $tableParams->getFirstSort()) {
            $queryBuilder->sort($firstSort[0], $firstSort[1]);
        }

        if ($services !== null && is_array($services)) {
            $queryBuilder->field('service.id')->in($services);
        }

        if ($request->get('deleted') == 'on') {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        $count = $queryBuilder->getQuery()->count();

        $queryBuilder->skip($tableParams->getStart())->limit($tableParams->getLength());

        /** @var \MBH\Bundle\PackageBundle\Document\PackageService[] $results */
        $results = $queryBuilder->getQuery()->execute()->toArray();

        $queryBuilder
            ->group(
                ['id' => 1],
                [
                    'result' => 0,
                    'amount' => 0,
                    'nights' => 0,
                    'guests' => 0,
                ]
            )->reduce(
                'function (obj, prev) {
                    var price = 0;
                    var amount = 0;
                    var nights = 0;
                    var persons = 0;

                    if (obj.totalOverwrite) {
                        price = obj.totalOverwrite;
                    } else {
                        price = obj.total;
                    }
                    if (!isNaN(parseInt(obj.amount))) {
                        amount = parseInt(obj.amount);
                    }
                    if (!isNaN(parseInt(obj.nights))) {
                        nights = parseInt(obj.nights);
                    }
                    if (!isNaN(parseInt(obj.persons))) {
                        persons = parseInt(obj.persons);
                    }
            
                    prev.result += parseInt(price) !== NaN ? parseInt(price) : 0;
                    prev.amount += amount;
                    prev.nights += nights;
                    prev.guests += persons;
                }'
            );

        $totals = iterator_to_array($queryBuilder->getQuery()->execute());
        if (isset($totals[0])) {
            $totals = $totals[0];
            $totals['result'] = number_format($totals['result'], 2);
        } else {
            $totals = [
                'nights' => 0,
                'guests' => 0,
                'amount' => 0,
                'result' => 0
            ];
        }

        if ($request->get('deleted') == 'on') {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return [
            'results' => $results,
            'recordsFiltered' => $count,
            'totals' => json_encode($totals),
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }
}