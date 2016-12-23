<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData

 */
class HotelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const HOTELS = [
        'hotel-one' => [
            'title' => 'Мой отель #1',
            'default' => true
        ],
        'hotel-two' => [
            'title' => 'Мой отель #2',
            'default' => false
        ]
    ];
    
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $repo = $manager->getRepository('MBHHotelBundle:Hotel');

        if (!count($repo->findAll())) {
            foreach (self::HOTELS as $key => $hotelData) {
                $hotel = new Hotel();
                $hotel
                    ->setFullTitle($hotelData['title'])
                    ->setIsDefault($hotelData['default'])
                ;

                $manager->persist($hotel);
                $manager->flush();

                $this->setReference($key, $hotel);
            }
        }
    }

    public function getOrder()
    {
        return -9999;
    }
}