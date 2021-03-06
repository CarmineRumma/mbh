<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\TaskTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DailyTaskType

 */
class DailyTaskType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hotel = $options['hotel'];
        $queryBuilderFunction = function(TaskTypeRepository $repository) use($hotel) {
            return $repository->createQueryBuilder()->field('hotel.id')->equals($hotel->getId());
        };

        $builder
            ->add('day', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'style' => 'width:60px',
                    'placeholder' => 'mbhhotelbundle.form.dailytasktype.dney',
                    'min' => 1,
                    'max' => 60
                ],
            ])
            ->add('taskType', DocumentType::class, [
                'required' => true,
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'group_by' => 'category',
                'attr' => [
                    'style' => 'width:250px',
                    'data-placeholder' => $this->translator->trans('mbhhotelbundle.form.dailytasktype.vyberite.uslugu')
                ],
                'placeholder' => '',
                'query_builder' => $queryBuilderFunction
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\DailyTaskSetting',
            'hotel' => null
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotel_daily_task';
    }
}