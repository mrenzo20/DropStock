<?php

namespace DropstockBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class SiteType extends AbstractType
{
  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('url')
      ->add('platform')
      ->add('status')
      ->add('checked')
      ->add('token')
      ->add('crypt')
      ->add('modules',
            CollectionType::class,
            array(
              'entry_type'   => TextType::class,
              'entry_options'  => array(
                'required'  => true,
                'attr'      => array('class' => 'email-box')
              ),
            )
      )
      
      ->add('data')
      ;

  }
  
  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
                             'data_class' => 'DropstockBundle\Entity\Site'
                           ));
  }
}
