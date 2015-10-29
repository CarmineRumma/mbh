<?php

namespace MBH\Bundle\CashBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;


class Cash
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Not confirmed user cash docs
     * @return array
     */
    public function notConfirmedCashDocuments()
    {
        $result = [
            'count' => 0,
            'totalIn' => 0,
            'totalOut' => 0,
            'total' => 0,
            'docs' => []
        ];

        $ch = $this->container->get('security.authorization_checker');
        if (!$ch->isGranted('IS_AUTHENTICATED_FULLY') && !$ch->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $result;
        }

        $docs =  $this->container
            ->get('doctrine_mongodb')
            ->getRepository('MBHCashBundle:CashDocument')
            ->findBy([
                'isConfirmed' => false,
                'method' => 'cash',
                'isPaid' => true,
                'deletedAt' => null,
                'createdBy' => $this->container->get('security.token_storage')->getToken()->getUser()->getUsername()
            ])
        ;

        foreach ($docs as $cash) {
            if ($cash->getOperation() == 'in') {
                $result['totalIn'] += $cash->getTotal();
            } else {
                $result['totalOut'] += $cash->getTotal();
            }
        }

        $result['count'] = count($docs);
        $result['docs'] = $docs;
        $result['total'] = $result['totalIn'] - $result['totalOut'];

        return $result;
    }

}