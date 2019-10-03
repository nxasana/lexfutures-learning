<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class JudgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('justiceTitle', EntityType::class, ['required' => true, 'class' => 'AppBundle:JusticeTitle', 'placeholder' => ''])
            ->add('file', FileType::class, ['label' => "Bio Picture", "required" => false, 'mapped' => false])
            ->add('file2', FileType::class, ['label' => "Judges Bio PDF", "required" => false, 'mapped' => false])
            ->add('profileLine1', TextareaType::class, ['required' => false, "label" => "field 1"])    
            ->add('profileLine2', TextareaType::class, ['required' => false, "label" => "field 2"])  
            ->add('profileLine3', TextareaType::class, ['required' => false, "label" => "field 3"])  
            ->add('profileLine4', TextareaType::class, ['required' => false, "label" => "field 4"])  
            ->add('profileLine5', TextareaType::class, ['required' => false, "label" => "field 5"])  
            ;
    }
    
    public function getBlockPrefix()
    {
        return 'app_judge_add';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Judge'
        ));
    }
}