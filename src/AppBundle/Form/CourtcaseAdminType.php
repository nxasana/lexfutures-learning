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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CourtcaseAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('jurisdiction', EntityType::class, ['class' => 'AppBundle:Jurisdiction', 'empty_data'  => null, 'placeholder' => 'Please select the Jurisdiction', 'label' => 'Jurisdiction'])
            ->add('scheduledDate', DateTimeType::class, ['required' => true, 'widget' => 'single_text'])
            ->add('isLeague', ChoiceType::class, ['required' => true, 'choices'  => ['Yes' => true, 'No' => false]])
            //->add('scheduledDate', DateTimeType::class, ['required' => true, 'input' => 'string'])
            ;
    }
    
    public function getBlockPrefix()
    {
        return 'app_courtcase_admin_add';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Courtcase'
        ));
    }
}