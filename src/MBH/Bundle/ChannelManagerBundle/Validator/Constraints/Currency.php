<?php
namespace MBH\Bundle\ChannelManagerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Currency extends Constraint
{
    public $message = 'validator.default_currency';

    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}