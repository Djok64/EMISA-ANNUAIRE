<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\Student;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('object', null, [
        'label' => 'Objet',
      ])
      ->add('content', null, [
        'label' => 'Votre message',
      ])
      ->add('expeditor', null, [
        'label' => 'Expéditeur',
      ])
      ->add('student', EntityType::class, [
        'class' => Student::class,
        'choice_label' => 'Firstname',
        'label' => 'Destinataire',
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Message::class,
    ]);
  }
}
