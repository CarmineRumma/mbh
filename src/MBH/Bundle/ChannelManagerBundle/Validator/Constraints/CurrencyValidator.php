<?php

namespace MBH\Bundle\ChannelManagerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CurrencyValidator extends ConstraintValidator
{

    /**
     * @param mixed $doc
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($doc, Constraint $constraint)
    {
        if($doc->getCurrency() && !$doc->getCurrencyDefaultRatio()) {

            $this->context->buildViolation($constraint->message)
                ->atPath('currencyDefaultRatio')
                ->addViolation();
        }

        return true;
    }

}
