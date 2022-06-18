<?php

namespace App\EventSubscriber;

use App\Domain\Feedback\Event\FeedbackCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;
    private TranslatorInterface $translator;

    public function __construct(
        MailerInterface     $mailer,
        TranslatorInterface $translator,
    )
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FeedbackCreatedEvent::NAME => 'sendEmailMessage',
        ];
    }

    public function sendEmailMessage(FeedbackCreatedEvent $event): void
    {
        $feedback = $event->getFeedback();

        $email = (new Email())
            ->to($feedback->getEmail())
            ->subject($this->translator->trans('feedback.notification.subject'))
            ->text($this->translator->trans('feedback.notification.text'));

        // https://symfony.com/doc/current/mailer.html#sending-messages-async
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // TODO: put in the queue and retry later.
        }
    }
}
