<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomStatus;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TaskData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getRoomStatuses()
    {
        return [
            [
                'code' => 'repair',
                'title' => 'Ремонт',
            ],
            [
                'code' => 'cleaning',
                'title' => 'Уборка',
            ],
            [
                'code' => 'reserve',
                'title' => 'Резерв',
            ],
            [
                'code' => 'other',
                'title' => 'Другое',
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            $this->persistForHotel($manager, $hotel);
        }
        $manager->flush();
    }

    public function persistForHotel(ObjectManager $manager, Hotel $hotel)
    {
        $roomStatusRepository = $manager->getRepository('MBHHotelBundle:RoomStatus');
        $taskTypeCategoryRepository = $manager->getRepository('MBHHotelBundle:TaskTypeCategory');
        $taskTypeRepository = $manager->getRepository('MBHHotelBundle:TaskType');

        $repairStatusList = [];
        foreach($this->getRoomStatuses() as $roomStatus) {
            $repairStatus = new RoomStatus();
            $repairStatus->setCode($roomStatus['code'])->setTitle($roomStatus['title'])->setHotel($hotel);
            $isNotExists = $roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($repairStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0;

            if ($isNotExists) {
                $manager->persist($repairStatus);
                $repairStatusList[$repairStatus->getCode()] = $repairStatus;
            }
        }

        $category = new TaskTypeCategory();

        $category->setIsSystem(true)
            ->setCode('clean')
            ->setTitle('Уборка')
            ->setFullTitle('Уборка помещений')
            ->setHotel($hotel);

        $taskType = new TaskType();
        $staff = $manager->getRepository('MBHUserBundle:Group')->findOneBy(['code' => 'staff']);
        $taskType->setIsSystem(true)
            ->setCode('clean_room')
            ->setTitle('Убрать комнату')
            ->setCategory($category)
            ->setDefaultUserGroup($staff)
            ->setHotel($hotel);
        if(isset($repairStatusList['cleaning'])) {
            $taskType->setRoomStatus($repairStatusList['cleaning']);
        }

        if ($taskTypeCategoryRepository->createQueryBuilder()
                ->field('code')->equals($category->getCode())
                ->field('hotel.id')->equals($hotel->getId())
                ->getQuery()->count() == 0
        ) {
            $manager->persist($category);
        }
        if ($taskTypeRepository->createQueryBuilder()
                ->field('code')->equals($taskType->getCode())
                ->field('hotel.id')->equals($hotel->getId())
                ->getQuery()->count() == 0
        ) {
            $manager->persist($taskType);
        }
    }
}