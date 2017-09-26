<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Service\ClientLimitsManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LimitsDashboardSource extends AbstractDashboardSource
{
    /**
     * message default type
     */
    const TYPE = 'danger';

    /** @var  ClientLimitsManager */
    private $limitsManager;
    /** @var  Router */
    private $router;

    public function __construct(
        ManagerRegistry $documentManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        Helper $helper,
        ClientLimitsManager $limitsManager,
        Router $router
    ) {
        parent::__construct($documentManager, $validator, $translator, $helper);
        $this->limitsManager = $limitsManager;
        $this->router = $router;
    }

    protected function generateMessages(): array
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 1 year');
        $messages = [];
        if ($this->limitsManager->isLimitOfRoomsExceeded()) {
            $messages[] = $this->translator->trans('room_controller.limit_of_rooms_exceeded');
        }

        $outOfLimitRoomsDays = $this->limitsManager->getDaysWithExceededLimitNumberOfRoomsInSell($begin, $end);
        if (count($outOfLimitRoomsDays) > 0) {
            $messages[] = $this->translator
                ->trans('room_cache_controller.limit_of_rooms_exceeded', [
                    '%busyDays%' => join(', ', $outOfLimitRoomsDays),
                    '%availableNumberOfRooms%' => $this->limitsManager->getAvailableNumberOfRooms(),
                    '%overviewUrl%' => $this->generateUrl('total_rooms_overview')
                ]);
        }

        return $messages;
    }
}