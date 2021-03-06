<?php

namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;

/**
 * Class HotelData
 */
class HotelData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Get hotel data
     *
     * @return array
     */
    const HOTELS_DATA = [
        'hotel-one' => [
            'title' => 'mbhhotelbundle.hotelData.hotelOne',
            'default' => true
        ],
        'hotel-two' => [
            'title' => 'mbhhotelbundle.hotelData.hotelTwo',
            'default' => false
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $repo = $manager->getRepository('MBHHotelBundle:Hotel');

        if (!count($repo->findAll())) {
            foreach (self::HOTELS_DATA as $key => $hotelData) {
                $hotel = new Hotel();
                $hotel
                    ->setFullTitle($this->container->get('translator')->trans($hotelData['title']))
                    ->setIsDefault($hotelData['default']);

                $manager->persist($hotel);
                $manager->flush();

                $this->setReference($key, $hotel);

                if ($this->getEnv() != 'test') {
                    break;
                }
            }
        }
    }

    public function getOrder()
    {
        return -9999;
    }
}
