<?php

namespace App\MessageHandler;

use App\SpamChecker;
use App\ImageOptimizer;
use Psr\Log\LoggerInterface;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Notification\CommentReviewNotification;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
  private $spamChecker;
  private $entityManager;
  private $commentRepository;
  private $bus;
  private $workflow;
  private $notifier;
  private $logger;

  public function __construct(EntityManagerInterface $entityManager,
                              SpamChecker $spamChecker,
                              CommentRepository $commentRepository,
                              MessageBusInterface $bus,
                              WorkflowInterface $commentStateMachine,
                              NotifierInterface $notifier,
                              ImageOptimizer $imageOptimizer,
                              string $photoDir,
                              LoggerInterface $logger = null)
  {
    $this->entityManager = $entityManager;
    $this->spamChecker = $spamChecker;
    $this->commentRepository = $commentRepository;
    $this->bus = $bus;
    $this->commentStateMachine = $commentStateMachine;
    $this->notifier = $notifier;
    $this->imageOptimizer = $this->imageOptimizer;
    $this->photoDir = $photoDir;
    $this->logger = $logger;
  }

  public function __invoke(CommentMessage $message)
  {
    $comment = $this->commentRepository->find($message->getId());
    if (!$comment) {
      return;
    }

    if ($this->workflow->can($comment, 'accept')) {
      $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
      $translation = 'accept';
      if (2 === $score) {
        $translation = 'reject_spam';
      } elseif (1 === $score) {
        $translation = 'might_be_spam';
      }

      $this->workflow->applay($comment, $translation);
      $this->entityManager->flush();
      $this->bus->dispatch($message);
    } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
      $notification = new CommentReviewNotification($comment, $message->getReviewUrl());
      $this->notifier->send($notification, ...$this->notifier->getAdminRecipients());
    } elseif ($this->workflow->can($comment, 'optimize')) {
      if ($comment->getPhotoFilename()) {
        $this->imageOptimizer->resize($this->photoDir . '/' . $comment->getPhotoFilename());
      }
      $this->workflow->apply($comment, 'optimize');
      $this->entityManager->flush();
    } elseif ($this->logger) {
      $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
    }
  }
}