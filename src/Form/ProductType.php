<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('color')
            ->add('size')
            ->add('type')
            ->add('price', NumberType::class, [
                'required' => false,
                'scale' => 2, // Nombre de décimales
                'attr' => [
                    'step' => 0.01, // Supporte les valeurs décimales
                    'min' => 0, // Optionnel : limite inférieure
                ],
            ])
            ->add('availability')
            ->add('description')
            ->add('media', FileType::class, [
                'label' => 'Product Media (Images, Videos)',
                'mapped' => false, // Not mapped to the Product entity directly
                'multiple' => true, // Allow multiple files
                'required' => false, // Optional
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
