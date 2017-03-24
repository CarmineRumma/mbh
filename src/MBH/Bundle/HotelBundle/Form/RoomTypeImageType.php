<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoomTypeImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fileText = 'views.hotel.form.RoomTypeType.image_type_number_for_online_booking';

        $builder->
            add('imageFile', FileType::class, ['label' => 'form.roomTypeType.image',
                'required' => false,
                'help' => $fileText,
                'constraints' => [new Image(), new NotBlank()],
                'attr' => ['multiple' => 'multiple']
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_room_type_image_type';
    }
}