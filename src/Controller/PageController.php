<?php

namespace App\Controller;

use App\Entity\Page;
use App\Form\PageType;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/page')]
final class PageController extends AbstractController
{
  #[Route(name: 'app_page_index', methods: ['GET'])]
  public function index(PageRepository $pageRepository): Response
  {
    return $this->render('page/index.html.twig', [
      'pages' => $pageRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_page_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $page = new Page();
    $form = $this->createForm(PageType::class, $page);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->persist($page);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Create',
        sprintf(
          'Create page ID %d: La page %s contenue: %s a été crée avec succès.',
          $page->getId(),
          $page->getTitle(),
          $page->getcontent()
        )
      );
      $this->addFlash('success', 'La page : ' . $page->getTitle() . '  a été crée avec succès');

      return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('page/new.html.twig', [
      'page' => $page,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_page_show', methods: ['GET'])]
  public function show(Page $page, LogService $logService): Response
  {
    $logService->log(
      $this->getUser(),
      'Read',
      sprintf(
        'Read page ID %d: La page %s contenue : %s a été consulté avec succès.',
        $page->getId(),
        $page->getTitle(),
        $page->getContent()
      )
    );
    return $this->render('page/show.html.twig', [
      'page' => $page,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_page_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Page $page, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $form = $this->createForm(PageType::class, $page);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Update',
        sprintf(
          'Update page ID %d: La page %s contenue : %s a été mise a jour avec succès.',
          $page->getId(),
          $page->getTitle(),
          $page->getContent()
        )
      );
      $this->addFlash('success', 'La page : ' . $page->getTitle() . '  a été mise a jour avec succès');

      return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('page/edit.html.twig', [
      'page' => $page,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_page_delete', methods: ['POST'])]
  public function delete(Request $request, Page $page, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($page);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Delete',
        sprintf(
          'Delete page ID %d: La page %s contenue : %s a été supprimé avec succès.',
          $page->getId(),
          $page->getTitle(),
          $page->getContent()
        )
      );
      $this->addFlash('success', 'La page :' . $page->getTitle() . '  a été supprimé avec succès');
    }

    return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
  }
}
