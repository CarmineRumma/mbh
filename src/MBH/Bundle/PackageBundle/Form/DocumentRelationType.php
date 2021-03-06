<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\VegaBundle\Service\DictionaryProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Class DocumentRelationType
 * @package MBH\Bundle\PackageBundle\Form

 */
class DocumentRelationType extends AbstractType
{
    /**
     * @var DictionaryProvider
     */
    private $dictionaryProvider;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function setDictionaryProvider(DictionaryProvider $dictionaryProvider)
    {
        $this->dictionaryProvider = $dictionaryProvider;
    }

    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @todo replace documentTypes by VagaDocumentType
     * @see \MBH\Bundle\VegaBundle\Document\VegaDocumentType
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dictTypes = array_keys($this->dictionaryProvider->getDictTypes());
        $documentTypes = array_map(['\MBH\Bundle\VegaBundle\Service\FriendlyFormatter', 'convertDocumentType'], $this->dictionaryProvider->getDocumentTypes());

        asort($documentTypes);

        //main
        if($options['citizenship']) {
            $builder
                ->add('citizenship', DocumentType::class, [
                    'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                    'label' => 'form.TouristExtendedType.citizenship',
                    'group' => 'form.DocumentRelation.citizenship',
                    'query_builder' => function(DocumentRepository $repository){
                        return $repository->createQueryBuilder()->sort(['name' => 1]);
                    },
                    'help' => $options['isFormInTouristController'] ? 'form.BirthplaceType.country.help' : null
                ]);
        }
        $builder
            ->add('type',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'choices' => $documentTypes,
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.type',
                'property_path' => 'documentRelation.type'
            ])
            ->add('series', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'required' => false,
                'label' => 'form.DocumentRelation.series',
                'property_path' => 'documentRelation.series'
            ])
            ->add('number', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'required' => false,
                'label' => 'form.DocumentRelation.number',
                'property_path' => 'documentRelation.number',
                'constraints' => [
                    new Type(['type' => 'numeric'])
                ]
            ])
        ;
        $builder
            ->add('authorityOrgan', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.authority',
                'required' => false,
                'property_path' => 'documentRelation.authorityOrgan'
            ])
            ->add('authorityOrganText', HiddenType::class, [
                'required' => false,
                'property_path' => 'documentRelation.authorityOrganText'
            ])
        ;
        $builder->get('authorityOrgan')->addModelTransformer(
            new EntityToIdTransformer($this->managerRegistry->getManager(), 'MBH\Bundle\VegaBundle\Document\VegaFMS')
        );
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();
            $authorityOrgan = $data['authorityOrgan'];
            if($authorityOrgan) {
                if(\MongoId::isValid($authorityOrgan)) {
                    $data['authorityOrganText'] = null;
                } else {
                    $data['authorityOrgan'] = null;
                    $data['authorityOrganText'] = $authorityOrgan;
                }
                $event->setData($data);
            }
        });

        $builder
            ->add('authorityOrganCode', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.authorityOrganCode',
                'required' => false,
                'property_path' => 'documentRelation.authorityOrganCode',
                'attr' => [
                    'style' =>  'width: 100px'
                ]
            ]);

        $builder
            ->add('issued', DateType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.issued',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
                'property_path' => 'documentRelation.issued'
            ])
            ->add('expiry', DateType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.expiry',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
                'property_path' => 'documentRelation.expiry'
            ])
            ->add('relation',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.relation',
                'choices' => array_combine($dictTypes, $dictTypes),
                'expanded' => true,
                'property_path' => 'documentRelation.relation'
            ]);

        //birthplace
        if($options['birthplace']) {
            $builder
                ->add('country', DocumentType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.country',
                    'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                    'query_builder' => function(DocumentRepository $repository){
                        return $repository->createQueryBuilder()->sort(['name' => 1]);
                    },
                    'placeholder' => '',
                    'required' => false,
                    'property_path' => 'birthplace.country',
                    'help' => $options['isFormInTouristController'] ? 'form.BirthplaceType.country.help' : null
                ])
                ->add('main_region', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.main_region',
                    'required' => false,
                    'property_path' => 'birthplace.main_region',
                    'attr' => ['class' => 'typeahead']
                ])
                ->add('district', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    /*'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                    'query_builder' => function(DocumentRepository $repository){
                        return $repository->createQueryBuilder()->sort(['name' => 1]);
                    },
                    'placeholder' => '',
                    */
                    'label' => 'form.BirthplaceType.district',
                    'required' => false,
                    'property_path' => 'birthplace.district'
                ])
                ->add('city', TextType::class, [//'mbh_city'
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.city',
                    'required' => false,
                    'property_path' => 'birthplace.city'
                ])
                ->add('settlement', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.settlement',
                    'required' => false,
                    'property_path' => 'birthplace.settlement'
                ]);
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
            //'cascade_validation' => true
            'citizenship' => true,
            'birthplace' => true,
            'isFormInTouristController' => false
        ]);
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'mbh_document_relation';
    }
}