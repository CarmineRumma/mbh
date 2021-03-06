<?php

namespace MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber;

/**
 * displays relationships between documents
 * Class Relationship
 * @package MBH\Bundle\BaseBundle\Service
 */
class Relationship
{
    /**
     * @var bool
     */
    private $isMany;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $documentClass;

    public function __construct($document, string $field, $message = null, $isMany = false)
    {
        $this->documentClass = $document;
        $this->fieldName = $field;
        $this->errorMessage = $message;
        $this->isMany = $isMany;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getDocumentClass()
    {
        return $this->documentClass;
    }

    /**
     * @return boolean
     */
    public function IsMany(): bool
    {
        return $this->isMany;
    }
}