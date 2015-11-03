<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PackageAccommodationType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PackageAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $optGroupRooms = $options['optGroupRooms'];
        $roomStatusIcons = $options['roomStatusIcons'];

        if ($options['roomType']) {
            $name = $options['roomType']->getName();
            uksort($optGroupRooms, function ($a, $b) use ($name) {
                if ($a == $name) {
                    return -1;
                }

                return 1;
            });
        }

        $builder
            ->add('accommodation', 'document', [
                'label' => 'form.packageAccommodationType.room',
                'required' => true,
                'empty_value' => '',
                'class' => 'MBHHotelBundle:Room',
                'group' => 'form.packageAccommodationType.choose_placement',
                'choices' => $optGroupRooms,
                'property' => 'name',
                'attr' => [
                    'class' => 'plain-html'
                ],
                'choice_attr' => function(Room $room) use($roomStatusIcons) {
                    $status = $room->getStatus();
                    return $status ? ['data-icon' => 'mbf-'.$roomStatusIcons[$status->getCode()]] : [];
                },
                'constraints' => new NotBlank()
            ])
            ->add('purposeOfArrival', 'choice', [
                'label' => 'form.packageMainType.arrival_purpose',
                'required' => false,
                'group' => 'form.packageAccommodationType.choose_placement',
                'multiple' => false,
                'choices' => $options['arrivals'],
                ])
            ->add('isCheckIn', 'checkbox', [
                'label' => 'form.packageAccommodationType.are_guests_checked_in',
                'value' => true,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'help' => 'form.packageAccommodationType.are_guests_checked_in_help'
            ])
            ->add('arrivalTime', 'datetime', array(
                'label' => 'form.packageMainType.check_in_time',
                'html5' => false,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'attr' => array('placeholder' => '12:00', 'class' => 'input-time'),
            ));
        $isCheckOutOptions = [
            'label' => 'form.packageAccommodationType.are_guests_checked_out',
            'value' => true,
            'group' => 'form.packageAccommodationType.check_in_group',
            'required' => false,
            'help' => 'form.packageAccommodationType.are_guests_checked_out_help'
        ];
        if ($options['debt']) {
            $isCheckOutOptions['attr'] = ['disabled' => 'disabled'];
            $isCheckOutOptions['help'] = 'form.packageAccommodationType.can_not_checkout_with_debt';
            $isCheckOutOptions['constraints'] = [
                new EqualTo(['value' => false])
            ];
        }
        $builder
            ->add('isCheckOut', 'checkbox', $isCheckOutOptions);
        $builder
            ->add('departureTime', 'datetime', array(
                'label' => 'form.packageMainType.check_out_time',
                'html5' => false,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'attr' => array('placeholder' => '12:00', 'class' => 'input-time'),
            ));

        $builder
            ->add('earlyCheckIn', 'hidden', [
                'required' => false,
                'mapped' => false,
            ])
            ->add('lateCheckOut', 'hidden', [
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'optGroupRooms' => [],
            'arrivals' => [],
            'roomType' => null,
            'debt' => false,
            'roomStatusIcons' => []
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_accommodation_type';
    }

}
