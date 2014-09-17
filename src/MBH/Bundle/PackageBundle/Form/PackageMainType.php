<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf('%02d', $i) . ':00';
        }

        $builder
                ->add('arrivalTime', 'choice', [
                    'label' => 'Время заезда',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $hours,
                ])
                ->add('departureTime', 'choice', [
                    'label' => 'Время отъезда',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $hours,
                ])
                ->add('purposeOfArrival', 'choice', [
                    'label' => 'Цель приезда',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $options['arrivals'],
                ])
                ->add('note', 'textarea', [
                    'label' => 'Комментарий',
                    'required' => false,
                ])
                
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'arrivals' => [],
            'defaultTime' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
