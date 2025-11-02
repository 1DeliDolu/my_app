<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Street Address',
                'constraints' => [new NotBlank()],
            ])
            ->add('city', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('state', TextType::class, [
                'required' => false,
            ])
            ->add('country', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'Postal Code',
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'label' => 'Phone Number',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Address Type',
                'choices' => [
                    'Shipping' => 'shipping',
                    'Billing' => 'billing',
                ],
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
