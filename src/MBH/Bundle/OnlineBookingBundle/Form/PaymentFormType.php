<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 9/30/16
 * Time: 11:02 AM
 */

namespace MBH\Bundle\OnlineBookingBundle\Form;


use MBH\Bundle\OnlineBookingBundle\Lib\SearchOrderParams;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class PaymentFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', TextType::class, [
                'required' => true,
                'label' => 'Номер заказа',
                'constraints' => [new NotBlank(), new NotNull(), new Length([
                    'min'=>1,
                    'max' => 2,
                ])]
            ])
            ->add('phone_email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ],
                'label' => 'Email/Phone'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Найти бронь'
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SearchOrderParams::class,
                'method' => Request::METHOD_POST,
                'attr' => [
                    'id' => 'payRestForm'
                ],
                'label' => 'Оплата заказа'
            ]);
    }


}