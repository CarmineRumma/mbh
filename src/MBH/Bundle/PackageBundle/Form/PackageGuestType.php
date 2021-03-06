<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageGuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tourist', TextType::class, [
                'label' => 'form.packageGuestType.fio',
                'required' => false,
                'group' => 'form.packageGuestType.find_guest',
                'attr' => [
                    'placeholder' => 'form.packageGuestType.placeholder_fio',
                    'style' => 'min-width: 500px',
                    'class' => 'findGuest'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'form.packageGuestType.surname',
                'required' => true,
                'group' => 'form.packageGuestType.add_guest',
                'attr' => ['placeholder' => 'form.packageGuestType.placeholder_surname'],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'form.packageGuestType.min_name',
                        'maxMessage' => 'form.packageGuestType.max_name'
                    ])
                ]
            ])
            ->add('firstName', TextType::class, [
                'label' => 'form.packageGuestType.name',
                'required' => true,
                'group' => 'form.packageGuestType.add_guest',
                'attr' => ['placeholder' => 'form.packageGuestType.placeholder_name'],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'form.packageGuestType.min_surname',
                        'maxMessage' => 'form.packageGuestType.max_surname'
                    ])
                ]
            ])
            ->add('patronymic', TextType::class, [
                'label' => 'form.packageGuestType.second_name',
                'required' => false,
                'group' => 'form.packageGuestType.add_guest',
                'attr' => ['placeholder' => 'form.packageGuestType.placeholder_second_name'],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'form.packageGuestType.min_second_name',
                        'maxMessage' => 'form.packageGuestType.max_second_name'
                    ])
                ]
            ])
            ->add('birthday', DateType::class, array(
                'label' => 'form.packageGuestType.birth_date',
                'widget' => 'single_text',
                'group' => 'form.packageGuestType.add_guest',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new Date()]
            ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_guest_type';
    }

}
