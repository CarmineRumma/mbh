<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HelpMessageTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('help', $options['help']);
        $builder->setAttribute('addon', $options['addon']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help'] = $form->getConfig()->getAttribute('help');
        $view->vars['addon'] = $form->getConfig()->getAttribute('addon');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['help', 'addon'])->setDefaults(['help' => null, 'addon' => null]);
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
?>