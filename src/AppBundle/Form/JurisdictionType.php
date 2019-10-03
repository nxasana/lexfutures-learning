<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Util\LegacyFormHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class JurisdictionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $seasons = $options['seasons'];
        
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('anname', TextType::class, ['required' => true, 'label' => 'AN Name']) 
            ->add('court', TextType::class, [])
            ->add('file', FileType::class, ['label' => "Bio Picture", "required" => false, 'mapped' => false])
            ->add('isActive', ChoiceType::class, ['choices'  => ['Yes' => 1, 'No' => 0]])
            ->add('currentSeason', EntityType::class, ['class' => 'AppBundle:Season', "required" => true, 'choices' => $seasons,])
            ;
    }
    
    public function getBlockPrefix()
    {
        return 'app_jurisdiction_edit';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Jurisdiction',
            'seasons' => false
        ]);
    }
}