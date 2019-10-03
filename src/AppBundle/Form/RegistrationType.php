<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class, ['attr' => array('placeholder' => "First Name")])
            ->add('last_name', TextType::class, ['attr' => array('placeholder' => "Last Name")])
            ->add('email', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\EmailType'), array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle', 'attr' => array('placeholder' => "Email Address")))
            ->add('plainPassword', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\RepeatedType'), array(
                'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\PasswordType'),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            //->add('educationLevel', EntityType::class, ['class' => 'AppBundle:EducationLevel', 'empty_data'  => null, 'placeholder' => 'Please select your education level'])
            ->add('occupation', EntityType::class, ['class' => 'AppBundle:Occupation', 'empty_data'  => null, 'placeholder' => 'Please select your category', 'label' => 'Category'])
            ->add('acceptTerms', CheckboxType::class, array('constraints' => new IsTrue(array("message" => "Please accept our Terms."))))
            ->add('country', CountryType::class, ["preferred_choices" => array('ZA'), 'required' => true])
            //->add('researchTerms', CheckboxType::class, ['required' => false])
            //->add('marketingTerms', CheckboxType::class, ['required' => false])
            ;
    }
    /*
    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }
    */
    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }
}