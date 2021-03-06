<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:channelmanager:configs')
            ->setDescription('Clear channel manager configs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $container = $this->getContainer();

        foreach($container->getParameter('mbh.channelmanager.services') as $title => $params) {

            if ($container->has($params['service']))
            {
                $service = $container->get($params['service']);
                $service->clearAllConfigs();
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}