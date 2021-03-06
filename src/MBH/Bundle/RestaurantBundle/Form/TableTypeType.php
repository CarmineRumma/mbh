<?php

namespace MBH\Bundle\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableTypeType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.category.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.table.category.form.fullTitle.placeholder'],
                'help' => 'restaurant.table.item.category.category_new'
            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.category.form.title.label',
                'required' => false,
                'help' => 'restaurant.category.form.title.help'
            ])

        ;

    }
    public function getBlockPrefix()
    {
        return 'mbh_bundle_restaurantbundle_table_category_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\TableType'
            ]);
    }


}