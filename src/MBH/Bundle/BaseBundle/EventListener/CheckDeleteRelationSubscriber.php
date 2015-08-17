<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\PackageBundle\Lib\DeleteException;

/**
 * Class CheckDeleteRelationSubscriber
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class CheckDeleteRelationSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove => 'preRemove',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws DeleteException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $dm = $args->getDocumentManager();

        $settings = $this->getSettings();
        if (array_key_exists(get_class($document), $settings)) {
            $settings = $settings[get_class($document)];

            foreach ($settings as $setting) {
                /** @var DocumentRepository $relationRepository */
                $relationRepository = $dm->getRepository($setting['document']);
                $count = $relationRepository->createQueryBuilder()
                    ->field($setting['field'] . '.id')->equals($document->getId())
                    ->field('deletedAt')->exists(false)
                    ->getQuery()->count();

                if ($count > 0) {
                    $message = isset($setting['message']) ? $setting['message'] : 'exception.relation_delete.message'; // have existing relation
                    throw new DeleteException($message, $count);
                }
            }
        }
    }

    public function getSettings()
    {
        return [
            TaskType::class => [
                [
                    'document' => Task::class,
                    'field' => 'type',
                    'message' => 'exception.relation_delete.message.task'
                ]
            ]
        ];
    }
}