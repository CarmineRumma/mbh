<?php


namespace MBH\Bundle\BaseBundle\Command;


use MBH\Bundle\BaseBundle\Lib\Task\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChannelManagerQueueGeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:queue:channelmanager:generate')
            ->setDescription('Generates RMQ messages for all clients ChannelManager Update')
            ->addOption('client', null, InputOption::VALUE_OPTIONAL, 'Specify client for channelManagerUpdate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $clients = $container->get('mbh.service.client_list_getter')->getClientsList();
        $certainClient = $input->getOption('client');
        if ($certainClient) {
            $clients = array_intersect($clients, [$certainClient]);
        }
        $kernel = $this->getContainer()->get('kernel');
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.task_command_runner_producer');
        foreach ($clients as $client) {
            $command = new Command(
                'mbh:channelmanager:pull',
                [],
                $client,
                $kernel->getEnvironment(),
                $kernel->isDebug()
            );
            $producer->publish(serialize($command));
        }
    }

}