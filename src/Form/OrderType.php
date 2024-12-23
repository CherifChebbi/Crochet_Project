<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { 
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['class' => 'form-control']
            ])
            ->add('customerAddress', TextareaType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control']
            ])
            ->add('customerPhone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'form-control']
            ])
            ->add('totalAmount', NumberType::class, [
                'label' => 'Total Amount',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Total Amount'],
            ])
            ->add('productIds', TextType::class, [
                'label' => 'Products',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Product IDs (comma-separated)'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Passer la commande',
                'attr' => ['class' => 'btn btn-success mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
