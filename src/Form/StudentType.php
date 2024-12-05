<?php

namespace App\Form;

use App\Entity\Student;
use App\Entity\Course;
use
  Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



class StudentType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('firstName')
      ->add('lastName')
      ->add('birthDay', null, [
        'widget' => 'single_text',
      ])
      //Selection de l'année volet deroulant 
      ->add('promo', ChoiceType::class, [
        'choices' => $this->getYearsRange(2024, 2050),
        'placeholder' => 'Sélectionnez une année',
        'label' => 'Année de la promo'
      ])
      // Systeme upload fichier il faut adapter le controller également
      ->add('photo', FileType::class, [
        'label' => 'Photo de profil (JPG/PNG)',
        'mapped' => false, // Non lié directement à l'entité
        'required' => false,
        //validation format et taille ci dessous
        'constraints' => [
          new Assert\File([
            'maxSize' => '2M',
            'mimeTypes' => [
              'image/jpeg',
              'image/png',
            ],
            'mimeTypesMessage' => 'Veuillez télécharger une image au format JPG ou PNG.',
          ]),
        ],
      ])
      ->add('company')
      ->add('description')
      ->add(
        'course',
        EntityType::class,
        [
          'class' => Course::class,
          'choice_label' => 'title'
        ]
      )

    ;
  }

  private function getYearsRange(int $start, int $end): array
  {
    $years = [];
    for ($year = $start; $year <= $end; $year++) {
      $years[$year] = $year;
    }
    return $years;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Student::class,
    ]);
  }
}
