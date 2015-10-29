<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;

/**
 * Class AutoTaskCreator
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class RoomTypeManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var ClientConfig;
     */
    private $config;

    /**
     * @var bool
     */
    public $useCategories = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);
        $this->useCategories = $this->config && $this->config ->getUseRoomTypeCategory();
    }

    /**
     * @return RoomTypeRepositoryInterface
     */
    public function getRepository()
    {
        $repoName = $this->useCategories ? 'MBHHotelBundle:RoomTypeCategory' : 'MBHHotelBundle:RoomType';

        return $this->dm->getRepository($repoName);
    }

    /**
     * @param Hotel $hotel
     * @param array $rooms
     * @return mixed
     */
    public function getRooms(Hotel $hotel = null, $rooms = null)
    {
        $repo = $this->getRepository();

        return $repo->fetch($hotel, $rooms);
    }


    /**
     * @param $id
     * @return RoomTypeInterface
     */
    public function findRoom($id)
    {
        return $this->getRepository()->find($id);
    }
}