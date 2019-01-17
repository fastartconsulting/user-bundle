<?php

namespace FAC\UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('keyword',          TextType::class, array('required'=>true, 'mapped'=>true))
            ->add('redirectUris',     TextType::class, array('required'=>false, 'mapped'=>true, 'empty_data'=>array()))
            ->add('allowedGrantTypes',TextType::class, array('required'=>false, 'mapped'=>true, 'empty_data'=>array()))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'FAC\UserBundle\Entity\Client',
        ));
    }

}