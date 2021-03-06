<?php

namespace  MBH\Bundle\BaseBundle\Twig;

use Doctrine\ODM\MongoDB\DocumentNotFoundException;

abstract class Template extends \Twig_Template
{
    protected function getAttribute($object, $item, array $arguments = array(), $type = \Twig_TemplateInterface::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        try {
            $ret = parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        } catch (DocumentNotFoundException $e) {
            $ret = null;
        }
        return $ret;
    }
}