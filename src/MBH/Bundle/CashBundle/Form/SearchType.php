<?php

namespace MBH\Bundle\CashBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SearchType
 * @package MBH\Bundle\CashBundle\Form

 */
class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, array(
                'label' => 'C',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => new \DateTime(),
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker begin-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()]
            ))
            ->add('end', DateType::class, array(
                'label' => 'mbhcashbundle.form.searchtype.po',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => null,
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker end-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()]
            ))
            ->add('sort',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhcashbundle.form.searchtype.sortirova',
                'required' => false,
            ])
            ->add('pay_type',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhcashbundle.form.searchtype.vid.platezha',
                'required' => false,
            ])
            ->add('show_no_paid', CheckboxType::class, [
                'required' => false,
            ])
            ->add('by_day', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getBlockPrefix()
    {
        return 's';
    }

}