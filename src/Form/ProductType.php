<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('color', TextType::class)
            ->add('size', TextType::class)
            ->add('type', TextType::class)
            ->add('price', NumberType::class)
            ->add('availability', ChoiceType::class, [
                'choices' => [
                    'Available' => true,
                    'Not Available' => false,
                ]
            ])
            ->add('description', TextareaType::class)
           
            // Media Collection - Display existing media files with delete button
            ->add('media', FileType::class, [
            'label' => 'Upload media files (image or video)',
            'multiple' => true,  // Allow multiple file uploads
            'mapped' => false,   // Not directly mapped to the entity field
            'required' => false
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
