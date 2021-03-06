<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:channelmanager:close')
            ->setDescription('Close channel manager services')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $container = $this->getContainer();

        foreach($container->getParameter('mbh.channelmanager.services') as $title => $params) {

            if (!$container->has($params['service'])) {
                continue;
            }

            $service = $container->get($params['service']);

            foreach ($dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
                $method = 'get' . $service::CONFIG;
                $config = $hotel->$method();

                if ($config && $config instanceof BaseInterface && !$config->getIsEnabled()) {
                    $service->closeForConfig($config);
                }
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}