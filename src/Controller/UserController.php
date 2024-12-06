<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\LogService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use
  Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user')]
final class UserController extends AbstractController
{
  //pour que lencodage du password soit accesible dans toutes la class
  private $passwordEncoder;
  public function
  __construct(UserPasswordHasherInterface
  $passwordEncoder)
  {
    $this->passwordEncoder = $passwordEncoder;
  }

  #[Route(name: 'app_user_index', methods: ['GET'])]
  public function index(UserRepository $userRepository): Response
  {
    return $this->render('user/index.html.twig', [
      'users' => $userRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
      //on commence l'encodage ici en declarant l'objet instancier par le constructeur
      $encodedPassword = $this->passwordEncoder->hashPassword($user, $user->getPassword()); //premier paramètre la clé d'encodage , second paramètre le password entré dans le formulaire
      $user->setPassword($encodedPassword); //on passe le password encodé pour le set dans la base de donnés
      $user->setCreatedAt(new \DateTimeImmutable());
      $user->setupdatedAt(new \DateTimeImmutable());
      $entityManager->persist($user);
      $entityManager->flush();

      $logService->log(
        $this->getUser(),
        'Create',
        sprintf(
          'Create user ID %d: L utilisateur %s  %s a été crée avec succès.',
          $user->getId(),
          $user->getEmail(),
          //ici on converti le json array en string
          implode(', ', $user->getRoles())
        )
      );
      $this->addFlash('success', 'L utilisateur : ' . $user->getEmail() . '  a été crée avec succès');

      return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('user/new.html.twig', [
      'user' => $user,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
  public function show(User $user, LogService $logService): Response
  {
    $logService->log(
      $this->getUser(),
      'Read',
      sprintf(
        'Read user ID %d: L utilisateur %s  %s a été crée avec succès.',
        $user->getId(),
        $user->getEmail(),
        implode(', ', $user->getRoles())
      )
    );

    return $this->render('user/show.html.twig', [
      'user' => $user,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, User $user, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Update',
        sprintf(
          'Update user ID %d: L utilisateur %s  %s a été mis à jour avec succès.',
          $user->getId(),
          $user->getEmail(),
          implode(', ', $user->getRoles())
        )
      );
      $this->addFlash('success', 'L utilisateur : ' . $user->getEmail() . '  a été mis à jour vec succès');

      return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('user/edit.html.twig', [
      'user' => $user,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
  public function delete(Request $request, User $user, EntityManagerInterface $entityManager, LogService $logService): Response
  {
    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($user);
      $entityManager->flush();
      $logService->log(
        $this->getUser(),
        'Delete',
        sprintf(
          'Delete user ID %d: L utilisateur %s  %s a été supprimé avec succès.',
          $user->getId(),
          $user->getEmail(),
          implode(', ', $user->getRoles())
        )
      );
      $this->addFlash('success', 'L utilisateur : ' . $user->getEmail() . '  a été supprimé avec succès');
    }

    return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
  }
}
