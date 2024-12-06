<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/message')]
final class MessageController extends AbstractController
{
  #[Route(name: 'app_message_index', methods: ['GET'])]
  public function index(MessageRepository $messageRepository,): Response
  {
    return $this->render('message/index.html.twig', [
      'messages' => $messageRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $message = new Message();
    $form = $this->createForm(MessageType::class, $message);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $message->setCreatedAt(new \DateTimeImmutable());

      $entityManager->persist($message);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Create',
        sprintf(
          'Create Message ID %d: Le Message de %s objet: %s a été créer avec succès.',
          $message->getId(),
          $message->getExpeditor(),
          $message->getCompleteMessage()
        )
      );
      $this->addFlash('success', 'Le Message a été créer avec succès');


      return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('message/new.html.twig', [
      'message' => $message,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
  public function show(Message $message, LogService $logService): Response
  {
    $logService->log(
      $this->getUser(),
      'Read',
      sprintf(
        'Read Message ID %d: Le Message de %s objet: %s a été consulté avec succès.',
        $message->getId(),
        $message->getExpeditor(),
        $message->getCompleteMessage()
      )
    );

    return $this->render('message/show.html.twig', [
      'message' => $message,
    ]);
  }

  //Les message ne peuvent pas être éditer par une autre personne cette fonction est donc supprimé
  // #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
  // public function edit(Request $request, Message $message, EntityManagerInterface $entityManager): Response
  // {
  //   $form = $this->createForm(MessageType::class, $message);
  //   $form->handleRequest($request);

  //   if ($form->isSubmitted() && $form->isValid()) {
  //     $entityManager->flush();

  //     return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
  //   }

  //   return $this->render('message/edit.html.twig', [
  //     'message' => $message,
  //     'form' => $form,
  //   ]);
  // }

  //la suppresion est conservé car les log pourront attester d'une suppression
  #[Route('/{id}', name: 'app_message_delete', methods: ['POST'])]
  public function delete(Request $request, Message $message, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    if ($this->isCsrfTokenValid('delete' . $message->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($message);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Delete',
        sprintf(
          'Delete Message ID %d: Le Message de %s %s a été supprimé avec succès.',
          $message->getId(),
          $message->getExpeditor(),
          $message->getCompleteMessage()
        )
      );
      $this->addFlash('success', 'Le Message a été créer avec succès');
    }

    return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
  }
}
