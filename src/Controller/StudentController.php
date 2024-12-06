<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use App\Service\LogService;
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
  public function new(Request $request, EntityManagerInterface $entityManager, LogService $logService): Response
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
      // Utilisation du service LogService
      $logService->log(
        $this->getUser(),
        'Create',
        sprintf(
          'Create student ID %d: L’étudiant %s %s a été créé avec succès.',
          $student->getId(),
          $student->getFirstName(),
          $student->getLastName()
        )
      );
      $this->addFlash('success', 'L étudiant : ' . $student->getFullName() . '  a été créer avec succès');


      return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('student/new.html.twig', [
      'student' => $student,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
  public function show(Student $student, LogService $logService): Response
  {
    $logService->log(
      $this->getUser(),
      'Read',
      sprintf(
        'Read student ID %d: L’étudiant %s %s a été consulté avec succès.',
        $student->getId(),
        $student->getFirstName(),
        $student->getLastName()
      )
    );
    return $this->render('student/show.html.twig', [
      'student' => $student,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Student $student, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $form = $this->createForm(StudentType::class, $student);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $student->setupdatedAt(new \DateTimeImmutable());
      $entityManager->flush();
      //ici on utilise le logger 
      $logService->log(
        $this->getUser(),
        'Update',
        sprintf(
          'Update student ID %d: L’étudiant %s %s a été mis a jours avec succès.',
          $student->getId(),
          $student->getFirstName(),
          $student->getLastName()
        )
      );
      $this->addFlash('success', 'L étudiant : ' . $student->getFullName() . '  a été mis a jour avec succès'); // est censé faire un message a l'utilisateur mais il faut modofier le html.twig associé du formulaire

      return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('student/edit.html.twig', [
      'student' => $student,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
  public function delete(Request $request, Student $student, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($student);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Delete',
        sprintf(
          'Delete student ID %d: L’étudiant %s %s a été supprimé avec succès.',
          $student->getId(),
          $student->getFirstName(),
          $student->getLastName()
        )
      );
      $this->addFlash('success', 'L étudiant : ' . $student->getFullName() . '  a été supprimé avec succès');
    }

    return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
  }
}
