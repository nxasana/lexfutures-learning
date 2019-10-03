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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['required' => true])
            ->add('lastName', TextType::class, ['required' => true])    
           // ->add('educationLevel', EntityType::class, ['required' => true, 'class' => 'AppBundle:EducationLevel', 'placeholder' => ''])
            ->add('occupation', EntityType::class, ['required' => true, 'class' => 'AppBundle:Occupation', 'placeholder' => ''])
            ->add('email', EmailType::class, ['label' => "Email Address", "required" => true])
            ->add('isFixture', ChoiceType::class, ['required' => false, 'choices'  => ['No' => false, 'Yes' => true]])
            ;
    }
    
    public function getBlockPrefix()
    {
        return 'user_edit';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }
}