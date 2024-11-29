<?php

namespace App\Form;

use App\Entity\Media;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', FileType::class, [
                'label' => 'Media File (Image or Video)',
                'mapped' => false, // We don't map the URL directly to the form field
                'required' => false,
            ])
            ->add('type', FileType::class, [
                'mapped' => false,
                'required' => false,
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
