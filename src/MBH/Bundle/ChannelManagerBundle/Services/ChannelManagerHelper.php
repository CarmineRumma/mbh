<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;

class ChannelManagerHelper
{
    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isTariffsSyncDataInit = false;
    private $tariffsSyncData;

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации типов комнат сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getRoomTypesSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {
        if (!$this->isRoomTypesInit) {

            foreach ($config->getRooms() as $room) {
                /** @var Room $room */
                $roomType = $room->getRoomType();
                if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                    continue;
                }

                if ($byService) {
                    $this->roomTypes[$room->getRoomId()] = [
                        'syncId' => $room->getRoomId(),
                        'doc' => $roomType
                    ];
                } else {
                    $this->roomTypes[$roomType->getId()] = [
                        'syncId' => $room->getRoomId(),
                        'doc' => $roomType
                    ];
                }
            }

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }

    /**
     * Метод формирования периодов(цен, ограничений, доступности комнат) из массива данных о ценах,
     * ограничениях или доступности комнат.
     *
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $entitiesByDates
     * @param array $comparePropertyMethods Массив имен методов, используемых для сравнения переданных сущностей
     * @return array
     */
    public function getPeriodsFromDayEntities(
        \DateTime $begin,
        \DateTime $end,
        array $entitiesByDates,
        array $comparePropertyMethods
    ) {
        $periods = [];
        $currentPeriod = [
            'begin' => $begin,
            'end' => $end,
            'entity' => null
        ];
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            /** @var \DateTime $day */
            $dayString = $day->format('d.m.Y');
            $dateEntity = isset($entitiesByDates[$dayString]) ? $entitiesByDates[$dayString] : null;
            if ($this->isEntityEquals($currentPeriod['entity'], $dateEntity, $comparePropertyMethods)) {
                $currentPeriod['end'] = $day;
            } else {
                $periods[] = $currentPeriod;
                $currentPeriod = [
                    'begin' => $day,
                    'end' => $day,
                    'entity' => $dateEntity
                ];
            }
        }

        if ($currentPeriod['end'] > end($periods)['end']) {
            $periods[] = $currentPeriod;
        }

        return $periods;
    }

    public function getMbhRoomTypeByServiceRoomTypeId($roomTypeId, ChannelManagerConfigInterface $config) : RoomType
    {
        foreach ($config->getRooms() as $room) {
            /** @var Room $room */
            if ($room->getRoomId() == $roomTypeId) {
                return $room->getRoomType();
            }
        }

        return null;
    }

    /**
     * Сортируем сущности цен, ограничений и доступности комнат по дате
     *
     * @param array $entities
     * @return array
     */
    public function sortEntitiesByDate(array $entities) : array
    {
        usort($entities, function ($a, $b) {
            return ($a->getDate() < $b->getDate()) ? -1 : 1;
        });

        return $entities;
    }

    private function isEntityEquals($firstEntity, $secondEntity, $comparePropertyMethods)
    {
        if (is_null($firstEntity) xor is_null($secondEntity)) {
            return false;
        } elseif (is_null($firstEntity) && is_null($secondEntity)) {
            return true;
        }

        $isEqual = true;
        foreach ($comparePropertyMethods as $comparePropertyMethod) {
            if ($firstEntity->{$comparePropertyMethod}() != $secondEntity->{$comparePropertyMethod}()) {
                $isEqual = false;
            }
        }

        return $isEqual;
    }

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации тарифов сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getTariffsSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {
        if (!$this->isTariffsSyncDataInit) {

            foreach ($config->getTariffs() as $configTariff) {
                /** @var \MBH\Bundle\ChannelManagerBundle\Document\Tariff $configTariff */
                $tariff = $configTariff->getTariff();

                if ($configTariff->getTariffId() === null || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                    continue;
                }

                if ($byService) {
                    $this->tariffsSyncData[$configTariff->getTariffId()] = [
                        'syncId' => $configTariff->getTariffId(),
                        'doc' => $tariff
                    ];
                } else {
                    $this->tariffsSyncData[$tariff->getId()] = [
                        'syncId' => $configTariff->getTariffId(),
                        'doc' => $tariff
                    ];
                }
            }

            $this->isTariffsSyncDataInit = true;
        }

        return $this->tariffsSyncData;
    }

}