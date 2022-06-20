<?php

namespace App\Domain\Feedback\UseCase;

use App\Domain\Feedback\Dto\CreateFeedbackDto;
use App\Domain\Feedback\Event\FeedbackCreatedEvent;
use App\Domain\Feedback\Exception\BadFeedbackDataException;
use App\Domain\Feedback\Feedback;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateFeedback implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ValidatorInterface       $validator,
        EntityManagerInterface   $entityManager,
        DenormalizerInterface    $denormalizer,
        EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->denormalizer = $denormalizer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws BadFeedbackDataException
     */
    public function __invoke(CreateFeedbackDto $data): Feedback
    {
        $this->validateOrThrow($data);

        $feedback = $this->makeFeedback($data);

        $this->storeFeedback($feedback);

        $this->eventDispatcher->dispatch(new FeedbackCreatedEvent($feedback), FeedbackCreatedEvent::NAME);

        return $feedback;
    }

    /**
     * @throws BadFeedbackDataException
     */
    protected function validateOrThrow(CreateFeedbackDto $data): void
    {
        $errors = $this->validator->validate($data);

        if ($errors->count()) {
            throw new BadFeedbackDataException($errors);
        }
    }

    protected function makeFeedback(CreateFeedbackDto $data): Feedback
    {
        try {
            return $this->denormalizer->denormalize($data, Feedback::class);
        } catch (ExceptionInterface $e) {
            $this->logger->error('Cannot denormalize Feedback DTO.', ['dto' => $data, 'exception' => $e]);
            throw new RuntimeException('Cannot denormalize feedback DTO.', previous: $e);
        }
    }

    protected function storeFeedback(Feedback $feedback): void
    {
        $this->entityManager->persist($feedback);
        $this->entityManager->flush();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
