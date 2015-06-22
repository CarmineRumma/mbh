<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Notifier service
 */
class Notifier implements \SplSubject
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \SplObjectStorage
     */
    private $observers;

    /**
     * @var NotifierMessage
     */
    private $message;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->observers = new \SplObjectStorage();
    }

    /**
     * @return NotifierMessage
     */
    public static function createMessage()
    {
        return new NotifierMessage();
    }

    /**
     * @param \SplObserver $observer
     * @return $this
     */
    public function attach(\SplObserver $observer)
    {
        $this->observers->attach($observer);

        return $this;
    }

    /**
     * @param NotifierMessage $message
     * @return $this
     */
    public function setMessage(NotifierMessage $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return NotifierMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \SplObserver $observer
     * @return $this
     */
    public function detach(\SplObserver $observer)
    {
        $this->observers->detach($observer);

        return $this;
    }

    /**
     * @return $this
     */
    public function notify()
    {
        if (!empty($this->message->getText())) {
            foreach ($this->observers as $observer) {
                try {
                    $observer->update($this);
                } catch (\Exception $e) {

                }
            }
        }
        $this->setMessage(null);

        return $this;
    }
}