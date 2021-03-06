<?php
namespace MBH\Bundle\CashBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CashDocument extends Constraint
{
    public $messageExpenseGreaterThanIncome = 'validator.constraints.expense_greater_than_income';
    public $messagePayer = 'validator.constraints.payer';
    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return get_class($this).'Validator';;
    }
}