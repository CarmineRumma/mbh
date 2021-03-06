<?php

namespace MBH\Bundle\BaseBundle\Lib;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;

class QueryBuilder extends Builder
{
    /**
     * @param DocumentManager $dm
     * @param null $documentName
     */
    public function __constructor(DocumentManager $dm, $documentName = null)
    {
        parent::__construct($dm, $documentName);
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function equalsNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->field($field)->equals($value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function notEqualsNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->field($field)->notEqual($value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function inToArray($field, $value)
    {
        is_array($value) ? $value : $value = [$value];
        $this->field($field)->in($value);

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function notInToArray($field, $value)
    {
        is_array($value) ? $value : $value = [$value];
        $this->field($field)->notIn($value);

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function inNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->inToArray($field, $value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function notInNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->notInToArray($field, $value);
        }

        return $this;
    }
}
