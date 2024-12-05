<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Log;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/student')]
final class StudentController extends AbstractController
{
  #[Route(name: 'app_student_index', methods: ['GET'])]
  public function index(StudentRepository $studentRepository): Response
  {
    return $this->render('student/index.html.twig', [
      'students' => $studentRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager): Response
  {
    $student = new Student();
    $form = $this->createForm(StudentType::class, $student);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      // Gestion de l'upload de la photo
      $photoFile = $form->get('photo')->getData();
      if ($photoFile) {
        // Générer un nom unique pour le fichier
        $newFilename = uniqid() . '.' . $photoFile->guessExtension();
        // Déplacer le fichier dans le répertoire configuré
        $photoFile->move(
          $this->getParameter('photos_directory'),
          $newFilename
        );
        // Sauvegarder le nom du fichier dans l'entité `Student`
        $student->setPhoto($newFilename);
      }

      $student->setCreatedAt(new \DateTimeImmutable());
      $student->setupdatedAt(new \DateTimeImmutable());
      $entityManager->persist($student);
      $entityManager->flush();
      $log = new Log($entityManager);
      $log->log(
        $this->getUser(),
        'Create',
        'Create student ID' . ' ' . $student->getId() . ' ' . 'L\étudiant ' . $student->getFirstName() . ' ' . $student->getLastName() . 'a était créer avec succès'
      );

      return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('student/new.html.twig', [
      'student' => $student,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
  public function show(Student $student, EntityManagerInterface $entityManager): Response
  {
    $log = new Log($entityManager);
    $log->log(
      $this->getUser(),
      'Read',
      'Read student ID' . ' ' . $student->getId() . ' ' . 'L\étudiant ' . $student->getFirstName() . ' ' . $student->getLastName() . 'a était consulté avec succès'
    );
    return $this->render('student/show.html.twig', [
      'student' => $student,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
  {
    $form = $this->createForm(StudentType::class, $student);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $student->setupdatedAt(new \DateTimeImmutable());
      $entityManager->flush();
      //ici on utilise le logger 
      $log = new Log($entityManager); //on instancie le logger ici on ne peu l'utiliser avec le constructeur etant une Entity
      $log->log( //on utilise la methode log 
        $this->getUser(), //on récupère l'utilisateur premier paramètre de log
        'Update', //second paramètre  rempli avec ce string
        'Update student ID' . ' ' . $student->getId() . ' ' .
          'L\étudiant ' . $student->getFirstName() . ' ' . $student->getLastName()
          . 'a était modifié avec succès' //3eme paramètre rempli avec ce string
      );
      $this->addFlash('success', 'L\étudiant a était modifié avec succès'); // est censé faire un message a l'utilisateur mais il faut modofier le html.twig associé du formulaire

      return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('student/edit.html.twig', [
      'student' => $student,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
  public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
  {
    if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($student);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
  }
}
