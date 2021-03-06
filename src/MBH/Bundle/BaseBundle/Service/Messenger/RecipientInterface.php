<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

/**
 * Interface RecipientInterface
 * @author Aleksandr Arofikin <sashaaro@gmaill.com>
 */
interface RecipientInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string|null
     */
    public function getCommunicationLanguage();
}