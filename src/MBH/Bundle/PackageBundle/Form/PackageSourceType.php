<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackageSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'form.packageSourceType.name',
                    'group' => 'form.packageSourceType.add_source',
                    'required' => true,
                    'attr' => ['placeholder' => 'form.packageSourceType.adds']
                ])
                ->add('title', 'text', [
                    'label' => 'form.packageSourceType.inner_name',
                    'group' => 'form.packageSourceType.add_source',
                    'required' => false,
                    'attr' => ['placeholder' => 'form.packageSourceType.inner_maxibooking_name']
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageSource',
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_packagepackagesourecetype';
    }

}
