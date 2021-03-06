<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 20.06.16
 * Time: 10:53
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.ingredient.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.ingredient.form.fullTitle.placeholder'],
                'help' => 'restaurant.ingredient.form.fullTitle.help',
                'group' => 'restaurant.group'

            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.ingredient.form.title.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.ingredient.form.title.placeholder'],
                'help' => 'restaurant.ingredient.form.title.help',
                'group' => 'restaurant.group'
            ])
            ->add('is_enabled', CheckboxType::class, [
                'label' => 'restaurant.ingredient.form.is_enable.label',
                'required' => false,
                'value' => true,
                'help' => 'restaurant.ingredient.form.is_enable.help',
                'group' => 'restaurant.group'
            ])
            ->add('price', TextType::class, [
                'label' => 'restaurant.ingredient.form.price.label',
                'required' => false,
                'attr' => ['class' => 'spinner price-spinner'],
                'help' => 'restaurant.ingredient.form.price.help',
                'group' => 'restaurant.collectprice'

            ])
            ->add('calcType',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'restaurant.ingredient.form.calcType.label',
                'required' => true,
                'placeholder' => '',
                'multiple' => false,
                'choices' => array_combine($options['calcTypes'],$options['calcTypes']),
                'help' => 'restaurant.ingredient.form.calcType.help',
                'group' => 'restaurant.collectprice'
            ])

            ->add('output', TextType::class, [
                'label' => 'restaurant.ingredient.form.output.label',
                'required' => true,
                'attr' => ['class' => 'fix-percent-spinner'],
                'help' => 'restaurant.ingredient.form.output.help',
                'group' => 'restaurant.collectprice'

            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\Ingredient',
                'calcTypes' => []
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_restaurant_ingredient_type';
    }

}