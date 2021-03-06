<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', TextType::class, [
                    'label' => 'form.packageSourceType.name',
                    'group' => 'form.packageSourceType.add_source',
                    'required' => true,
                    'attr' => ['placeholder' => 'form.packageSourceType.adds']
                ])
                ->add('title', TextType::class, [
                    'label' => 'form.packageSourceType.inner_name',
                    'group' => 'form.packageSourceType.add_source',
                    'required' => false,
                    'attr' => ['placeholder' => 'form.packageSourceType.inner_maxibooking_name']
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageSource',
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_packagepackagesourecetype';
    }

}
