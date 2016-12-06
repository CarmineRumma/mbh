<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class PriceData

 */
class ServiceData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const SERVICES = [
        'Питание' => [
            'Breakfast' => ['name' => 'Завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Continental breakfast' => ['name' => 'Континентальный завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'American breakfast' => ['name' => 'Американский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Buffet breakfast' => ['name' => 'Завтрак-буфет', 'calcType' => 'per_night', 'enabled' => false],
            'Full english breakfast' => ['name' => 'Полный английский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Lunch' => ['name' => 'Обед', 'calcType' => 'per_night', 'enabled' => false],
            'Dinner' => ['name' => 'Ужин', 'calcType' => 'per_night', 'enabled' => false],
            'Half board' => ['name' => 'Полупансион', 'calcType' => 'per_night', 'enabled' => false],
            'Breakfast and Lunch' => ['name' => 'Завтрак и обед', 'calcType' => 'per_night', 'enabled' => false],
            'Full board' => ['name' => 'Полный пансион', 'calcType' => 'per_night', 'enabled' => false],
            'Full pansion' => ['name' => 'Обед и ужин', 'calcType' => 'per_night', 'enabled' => false],
            'Breakfast for Children' => ['name' => 'Детский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Continental breakfast for Children' => ['name' => 'Детский континентальный завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'American breakfast for Children' => ['name' => 'Детский американский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Buffet breakfast for Children' => ['name' => 'Детский завтрак-буфет', 'calcType' => 'per_night', 'enabled' => false],
            'Full english breakfast for Children' => ['name' => 'Детский полный английский завтрак', 'calcType' => 'per_night', 'enabled' => false],
            'Lunch for Children' => ['name' => 'Детский обед', 'calcType' => 'per_night', 'enabled' => false],
            'Dinner for Children' => ['name' => 'Детский ужин', 'calcType' => 'per_night', 'enabled' => false],
            'Half board for Children' => ['name' => 'Детский полупансион', 'calcType' => 'per_night', 'enabled' => false],
            'Full board for Children' => ['name' => 'Детский полный пансион', 'calcType' => 'per_night', 'enabled' => false],
        ],
        'Размещение' => [
            'Extrabed' => ['name' => 'Дополнительная кровать', 'calcType' => 'per_night', 'enabled' => true],
            'Infant' => ['name' => 'Инфант', 'calcType' => 'per_night', 'enabled' => true],
            'Early check-in'  => ['name' => 'Ранний заезд', 'calcType' => 'day_percent', 'enabled' => true],
            'Late check-out'  => ['name' => 'Поздний выезд', 'calcType' => 'day_percent', 'enabled' => true],
        ],
        'Опции' => [
            'WiFi' => ['name' => 'WiFi', 'calcType' => 'per_night', 'enabled' => false],
            'Internet' => ['name' => 'Интернет', 'calcType' => 'per_night', 'enabled' => false],
            'Parking space' => ['name' => 'Парковка', 'calcType' => 'per_night', 'enabled' => false],
            'Babycot' => ['name' => 'Детская кровать', 'calcType' => 'per_night', 'enabled' => false],

        ],
        'Трансфер' => [
            'Transfer' => ['name' => 'Трансфер', 'calcType' => 'not_applicable', 'date' => true, 'time' => true, 'enabled' => false]
        ]
    ];
    
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            foreach (self::SERVICES as $catName => $services) {
                $category = $manager->getRepository('MBHPriceBundle:ServiceCategory')->findOneBy([
                    'system' => true,
                    'fullTitle' => $catName,
                    'hotel.id' => $hotel->getId()
                ])
                ;

                if (empty($category)) {
                    $category = new ServiceCategory();
                    $category->setSystem(true)
                        ->setIsEnabled(true)
                        ->setFullTitle($catName)
                        ->setHotel($hotel)
                    ;
                    $manager->persist($category);
                    $manager->flush();
                }

                foreach ($services as $code => $info) {
                    $service = $manager->getRepository('MBHPriceBundle:Service')->findOneBy([
                        'system' => true,
                        'code' => $code,
                        'category.id' => $category->getId()
                    ])
                    ;

                    if (empty($service)) {
                        $service = new Service();
                        $service->setCode($code)
                            ->setSystem(true)
                            ->setIsEnabled($info['enabled'])
                            ->setFullTitle($info['name'])
                            ->setPrice(0)
                            ->setCalcType($info['calcType'])
                            ->setDate(!empty($info['date']) ? $info['date'] : null)
                            ->setTime(!empty($info['time']) ? $info['time'] : null)
                            ->setCategory($category)
                        ;
                        $manager->persist($service);
                        $manager->flush();
                    }
                }
            }
        }
    }

    public function getOrder()
    {
        return 2;
    }
}