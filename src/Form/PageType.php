<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('title', null, [
        'label' => 'Titre',
      ])
      ->add('content', null, [
        'label' => 'Contenue',
      ])
      ->add('active')
      ->add('metaTitle')
      ->add('metaDescription')
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Page::class,
    ]);
  }
}
