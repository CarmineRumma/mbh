<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\DefaultTemplateGenerator;

/**
 * Class BillTemplateGenerator

 */
class BillTemplateGenerator extends DefaultTemplateGenerator
{
    protected function prepareParams(array $formData)
    {
        $params =  parent::prepareParams($formData);
        /** @var PackageService[] $packageServices */
        $packageServices = [];
        /** @var PackageServiceGroupByService[] $packageServicesByType */
        $packageServicesByType = []; //todo mongo aggregation, move to repository

        $total = 0;
        /** @var Order $order */
        $order = $formData['package']->getOrder();
        $packages = $order->getPackages();
        foreach($packages as $package) {
            $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
            $total += $package->getPackagePrice(true);
        }

        foreach($packageServices as $ps) {
            $service = $ps->getService();
            $groupBy = $ps->getPrice().$service->getId();
            if(!array_key_exists($groupBy, $packageServicesByType)) {
                $packageServicesByType[$groupBy] = new PackageServiceGroupByService($service, $ps->getPrice());
            }
            $packageServicesByType[$groupBy]->add($ps);
            $total += $ps->getTotal();
        }

        $dm = $this->container->get('doctrine_mongodb')->getManager();
        /** @var CashDocument[] $cashDocuments */
        $cashDocuments = $dm->getRepository('MBHCashBundle:CashDocument')->findBy(['order.id' => $order->getId()]);

        $token = $this->container->get('security.token_storage')->getToken();
        $user = null;
        if($token && $token->getUser()) {
            $user = $token->getUser();
        }

        return $params + [
            'total' => $total,
            'order' => $order,
            'packageServicesByType' => $packageServicesByType,
            'cashDocuments' => $cashDocuments,
            'user' => $user
        ];
    }
}