<?php

namespace UploadBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FichierType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tmpFile',FileType:: class)
            ->add('onedownload', ChoiceType::class, array(
                'choices'  => array(
                    'Oui' => 1,
                    'Non' => 0,
                ),
                'choices_as_values' => true,
            ))
            ->add('lifetime', ChoiceType::class, array(
                'choices'  => array(
                    '10 min' => 10,
                    '30 min' => 30,
                    '1 h'    => 60,
                ),
                'choices_as_values' => true,
            ))
            ->add('envoyer', SubmitType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UploadBundle\Entity\Fichier'
        ));
    }
}
