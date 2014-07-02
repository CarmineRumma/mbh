<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;

class RoomTypeGenerateRoomsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('from', 'text', [
                    'label' => 'Номер первой комнаты',
                    'required' => true,
                    'attr' => ['placeholder' => '1', 'class' => 'spinner'],
                    'constraints' => [
                        new NotBlank(),
                        new Type(['type' => 'numeric', "message" => 'Поле должно быть числом'])
                    ]
                ])
                ->add('to', 'text', [
                    'label' => 'Номер последней комнаты',
                    'required' => true,
                    'attr' => ['placeholder' => '100', 'class' => 'spinner'],
                    'constraints' => [
                        new NotBlank(),
                        new Type(['type' => 'numeric', "message" => 'Поле должно быть числом'])
                    ]
                ])
                ->add('prefix', 'text', [
                    'label' => 'Префикс',
                    'required' => false,
                    'attr' => ['placeholder' => 'HTL', 'class' => 'spinner'],
                    'help' => 'Префикс для названия номеров. Пример комнаты: HTL-12',
                    'constraints' => new Length(['max' => 20])
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                [ 'constraints' => [
                        new Callback(['methods' => [[get_class($this), 'rangeValidation']]])
                    ]]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_generate_rooms_type';
    }

    public static function rangeValidation($data, ExecutionContextInterface $context)
    {
        if ($data['from'] >= $data['to']) {
            $context->addViolation('Номер первой комнаты не может быть меньше номера последней.');
        }

        if ($data['to'] - $data['from'] > 500) {
            $context->addViolation('Слишком большое количество номеров для генерации.');
        }
    }

}
