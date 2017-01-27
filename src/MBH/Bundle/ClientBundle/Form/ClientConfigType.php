<?php

namespace MBH\Bundle\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientConfigType
 */
class ClientConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isSendSms', CheckboxType::class, [
                'label' => 'form.clientConfigType.sms_notification',
                'group' => 'form.clientConfigType.main_group',
                'value' => true,
                'required' => false,
                'help' => 'form.clientConfigType.is_sms_notification_turned_on'
            ])
            ->add('is_instant_search', CheckboxType::class, [
                'label' => 'form.clientConfigType.instant_search',
                'group' => 'form.clientConfigType.main_group',
                'help' => 'form.clientConfigType.instant_search_help',
                'required' => false,
            ])
            ->add('useRoomTypeCategory', CheckboxType::class, [
                'label' => 'form.clientConfigType.is_disabled_room_type_category',
                'group' => 'form.clientConfigType.main_group',
                'required' => false,
            ])
            ->add('searchDates', TextType::class, [
                'label' => 'form.clientConfigType.search_dates',
                'help' => 'form.clientConfigType.search_dates_desc',
                'group' => 'form.clientConfigType.search_group',
                'required' => true,
            ])
            ->add('searchTariffs', TextType::class, [
                'label' => 'form.clientConfigType.search_tariffs',
                'help' => 'form.clientConfigType.search_tariffs_desc',
                'group' => 'form.clientConfigType.search_group',
                'required' => true,
            ])
            ->add('searchWindows', CheckboxType::class, [
                'label' => 'form.clientConfigType.search_windows',
                'help' => 'form.clientConfigType.search_windows_desc',
                'group' => 'form.clientConfigType.search_group',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_clientbundle_client_config_type';
    }

}
