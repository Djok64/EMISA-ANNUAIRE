<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LogService
{
  private EntityManagerInterface $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }
  //fonction pour récupérer les log 
  public function log($user, string $actionType, string $description): void
  {
    $log = new Log($this->entityManager);
    $log->setAuthor($user->getEmail());
    $log->setActionType($actionType);
    $log->setDescription($description);
    $log->setCreatedAt(new \DateTimeImmutable());

    $this->entityManager->persist($log);
    $this->entityManager->flush();
  }
}
