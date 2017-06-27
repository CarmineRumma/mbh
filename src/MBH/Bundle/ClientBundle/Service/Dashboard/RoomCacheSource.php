<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

/**
 * RoomCacheSource - checks room cache
 */
class RoomCacheSource extends AbstractDashboardSource
{
    /**
     * route - url route
     */
    const ROUTE = 'room_cache_overview';
    
    /**
     * period - verification period
     */
    const PERIOD = 365;
    
    /**
     * @var array
     */
    private $messages = [];
    
    /**
     * @var array
     */
    private $dates = [];

    /**
     * @var array
     */
    private $caches;
    
    /**
     * {@inheritDoc}
     */
    protected function generateMessages(): array
    {
        $this->caches = $this->getCaches();

        foreach ($this->caches as $hotelData) {
            foreach ($hotelData as $roomData) {
                foreach ($roomData as $cache) {
                    $this->checkZeroes($cache);
                }
            }
        }
 
        $this->processDates();

        return $this->messages;
    }

    
    /**
     * process dates
     *
     * @return self
     */
    private function processDates(): self
    {
        $hotelRepo = $this->documentManager->getRepository('MBHHotelBundle:Hotel');
        $roomTypeRepo = $this->documentManager->getRepository('MBHHotelBundle:RoomType');

        foreach ($this->dates as $hotelId => $hotelData) {
            $hotel = $hotelRepo->find($hotelId);
            $message = $hotel->getName() . ': ' .
            $this->translator->trans('dashboard.messages.hotel.errors') . '<br>';
            foreach ($hotelData as $roomTypeId => $caches) {
                $roomType = $roomTypeRepo->find($roomTypeId);
                $message .= $roomType->getName() . ': ' .
                    $this->translator->trans('dashboard.messages.roomType.roomCache') . ' ' .
                    implode(', ', $this->getPeriods($caches)) . '<br>'
                ;
            }

            $this->messages[] = $message;
        }
        return $this;
    }

    
    /**
     * get dates periods
     *
     * @param array $caches
     * @return array
     */
    private function getPeriods(array $caches): array
    {
        $caches = array_values($caches);
        $result = [];
        foreach ($caches as $i => $cache) {
            if ($i == 0 || !$begin) {
                $begin = $cache[0]['date'];
            }
            $end = $cache[0]['date'];
            if (!isset($caches[$i + 1]) || (int) $caches[$i + 1][0]['date']->diff($end)->format('%a') != 1) {
                $message = $begin->format('d.m.Y');
                $message .=  $begin != $end ? '-' . $end->format('d.m.Y') : '';
                $result[] =  $message;

                $begin = $end = null;
            }
        }

        return $result;
    }

    /**
     * check roomCaches - zeroes
     *
     * @param array $cache
     * @return self
     */
    private function checkZeroes(array $cache): self
    {
        if (!$cache['totalRooms']) {
            $this->addDate($cache);
        }
        return $this;
    }
    
    /**
     * add cache to error dates
     *
     * @param array $cache
     * @return self
     */
    private function addDate(array $cache): self
    {
        $this->dates[$cache['hotel']][$cache['roomType']][$cache['date']->format('d.m.Y')][] = $cache;

        return $this;
    }

    /**
     * Get roomCaches for period
     *
     * @return array
     */
    private function getCaches(): array
    {
        return $this->documentManager->getRepository('MBHPriceBundle:RoomCache')
        ->findForDashboard(static::PERIOD);
    }
}
