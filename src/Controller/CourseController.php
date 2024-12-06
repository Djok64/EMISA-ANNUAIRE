<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/course')]
final class CourseController extends AbstractController
{
  #[Route(name: 'app_course_index', methods: ['GET'])]
  public function index(CourseRepository $courseRepository): Response
  {
    return $this->render('course/index.html.twig', [
      'courses' => $courseRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $course = new Course();
    $form = $this->createForm(CourseType::class, $course);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $course->setCreatedAt(new \DateTimeImmutable());
      $course->setUpdatedAt(new \DateTimeImmutable());

      $entityManager->persist($course);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Create',
        sprintf(
          'Create course ID %d: La formation %s %s a été créer avec succès.',
          $course->getId(),
          $course->getTitle(),
          $course->getDescription()
        )
      );
      $this->addFlash('success', 'La formation a été créer avec succès');

      return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('course/new.html.twig', [
      'course' => $course,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
  public function show(Course $course, LogService $logService): Response
  {
    $logService->log(
      $this->getUser(),
      'Read',
      sprintf(
        'Read course ID %d: La formation %s %s a été consulté avec succès.',
        $course->getId(),
        $course->getTitle(),
        $course->getDescription()
      )
    );
    return $this->render('course/show.html.twig', [
      'course' => $course,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Course $course, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $form = $this->createForm(CourseType::class, $course);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Update',
        sprintf(
          'Update course ID %d: La formation %s %s a été mise a jour avec succès.',
          $course->getId(),
          $course->getTitle(),
          $course->getDescription()
        )
      );
      $this->addFlash('success', 'La formation a été mise a jour avec succès');

      return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('course/edit.html.twig', [
      'course' => $course,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
  public function delete(Request $request, Course $course, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($course);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Delete',
        sprintf(
          'Delete course ID %d: La formation %s %s a été supprimé avec succès.',
          $course->getId(),
          $course->getTitle(),
          $course->getDescription()
        )
      );
      $this->addFlash('success', 'La formation a été supprimé avec succès');
    }

    return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
  }
}
