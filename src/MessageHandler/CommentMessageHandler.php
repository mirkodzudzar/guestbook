<?php

namespace App\MessageHandler;

use App\SpamChecker;
use Psr\Log\LoggerInterface;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;

class CommentMessageHandler implements MessageHandlerInterface
{
  private $spamChecker;
  private $entityManager;
  private $commentRepository;
  private $bus;
  private $workflow;
  private $mailer;
  private $adminEmail;
  private $logger;

  public function __construct(EntityManagerInterface $entityManager,
                              SpamChecker $spamChecker,
                              CommentRepository $commentRepository,
                              MessageBusInterface $bus,
                              WorkflowInterface $commentStateMachine,
                              MailerInterface $mailer,
                              string $adminEmail,
                              LoggerInterface $logger = null)
  {
    $this->entityManager = $entityManager;
    $this->spamChecker = $spamChecker;
    $this->commentRepository = $commentRepository;
    $this->bus = $bus;
    $this->commentStateMachine = $commentStateMachine;
    $this->mailer = $mailer;
    $this->adminEmail = $adminEmail;
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
      $this->mailer->send((new NotificationEmail())
        ->subject('New comment posted')
        ->htmlTemplate('emails/comment_notification.html.twig')
        ->from($this->adminEmail)
        ->to($this->adminEmail)
        ->context(['comment' => $comment])
      );
    } elseif ($this->logger) {
      $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
    }
  }
}