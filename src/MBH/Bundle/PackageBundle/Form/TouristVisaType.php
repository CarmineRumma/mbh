<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TouristVisaType

 */
class TouristVisaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'tourist.visa.type_type',
                'group' => 'visa',
                'placeholder' => '',
                'choices' => [
                    'visa' => 'Виза',
                    'residence' => 'Вид на жительство',
                    'temporary_residence_permit' => 'Разрешение на временное проживание'
                ],
                'required' => false,
            ])
            ->add('series', TextType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_series',
                'required' => false,
            ])
            ->add('number', TextType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_number',
                'required' => false,
            ])
            ->add('issued', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_issued',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ])
            ->add('expiry', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_expiry',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ])
            ->add('profession', TextType::class, [
                'label' => 'tourist.visa.type_profession',
                'group' => 'visa',
                'required' => false,
            ])
            ->add('arrivalTime', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.arrival_time',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ])
            ->add('departureTime', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.departure_time',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'mbh_package_tourist_visa';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Visa'
        ]);
    }
}