<?php

namespace MBH\Bundle\RestaurantBundle\Validator\Constraints;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TableValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    const AMOUNT_CHAIRS = 20;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \MBH\Bundle\RestaurantBundle\Document\Table $table
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($table, Constraint $constraint)
    {
        if ($table->getChairs()->count() > self::AMOUNT_CHAIRS) {
            $this->context->addViolation($constraint->messageError);
        }
        return true;
    }

}
