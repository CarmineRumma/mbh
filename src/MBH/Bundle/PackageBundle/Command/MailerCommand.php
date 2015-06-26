<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MailerCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:package:mailer')
            ->setDescription('Send report mails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $helper = $this->getContainer()->get('mbh.helper');
        $tr = $this->getContainer()->get('translator');
        $notifier = $this->getContainer()->get('mbh.notifier.mailer');

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }
        $now = new \DateTime('midnight');
        $tomorrow = clone $now;
        $tomorrow->modify('+1 day');
        $dayAfterTomorrow =  clone $tomorrow;
        $dayAfterTomorrow->modify('+1 day');

        $repo = $this->dm->getRepository('MBHPackageBundle:Package');

        //begin tomorrow report
        $packages = $repo->createQueryBuilder('p')
            ->field('begin')->gte($tomorrow)
            ->field('begin')->lt($dayAfterTomorrow)
            ->getQuery()
            ->execute();
        ;
        $transferCategories = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')->findBy([
           '$or' => [['fullTitle' => 'Трансфер'], ['title' => 'Трансфер']],
           'isEnabled' => true
        ]);
        $transferServices = $this->dm->getRepository('MBHPriceBundle:Service')->findBy([
                'category.id' => ['$in' => $helper->toIds($transferCategories)],
                'isEnabled' => true
            ]
        );

        $packageTransfers = $this->dm->getRepository('MBHPackageBundle:PackageService')
            ->createQueryBuilder('s')
            ->field('begin')->gte($tomorrow)
            ->field('begin')->lt($dayAfterTomorrow)
            ->field('service.id')->in($helper->toIds($transferServices))
            ->sort('service.id')
            ->getQuery()
            ->execute();

        if (count($packageTransfers) || count($packages)) {
            $message = $notifier::createMessage();
            $message
                ->setText('hide')
                ->setFrom('report')
                ->setSubject($tr->trans('mailer.report.arrival.subject'))
                ->setType('info')
                ->setCategory('report')
                ->setAdditionalData([
                    'packages' => $packages,
                    'transfers' => $packageTransfers,
                ])
                ->setTemplate('MBHBaseBundle:Mailer:reportArrival.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'))
            ;
            $notifier
                ->setMessage($message)
                ->notify()
            ;
        }

        //begin tomorrow users
        if (count($packages)) {
            foreach ($packages as $package) {
                $payer = $package->getOrder()->getPayer();
                if (!$payer || !$payer->getEmail()) {
                    continue;
                }

                $hotel = $package->getRoomType()->getHotel();
                $message = $notifier::createMessage();

                $message
                    ->setFrom('report')
                    ->setSubject($tr->trans('mailer.user.arrival.subject'))
                    ->setType('info')
                    ->setCategory('user')
                    ->setHotel($hotel)
                    ->setOrder($package->getOrder())
                    ->setAdditionalData([
                        'package' => $package,
                        'links' => $this->getContainer()->getParameter('mailer.user.arrival.links')
                    ])
                    ->setTemplate('MBHBaseBundle:Mailer:userArrival.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($payer->getEmail())
                    ->setLink('hide')
                    ->setSignature($tr->trans('mailer.online.user.signature', ['%hotel%' => $hotel]))
                ;
                $notifier
                    ->setMessage($message)
                    ->notify()
                ;

            }
        }


        $time = $start->diff(new \DateTime());
        $output->writeln('Installing complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

}