<?php

namespace App\Form;

use App\Entity\Media;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Add a file upload field
            ->add('url', FileType::class, [
                'required' => false,  // Optional, because the field can be left empty if no file is uploaded
                'label' => 'Upload media file (image or video)',
                'mapped' => false,    // This prevents it from trying to map the file to the entity's field
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Image' => 'image',
                    'Video' => 'video',
                ]
            ])
            ->add('product',EntityType::class,[
                'class'=>Product::class,
                'choice_label'=>'name',
                'multiple'=>false,//choix uniq ou mult
                'expanded'=>false,//liste- false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
