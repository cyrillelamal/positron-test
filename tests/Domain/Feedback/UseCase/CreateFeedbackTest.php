<?php

namespace App\Tests\Domain\Feedback\UseCase;

use App\Domain\Feedback\Dto\CreateFeedbackDto;
use App\Domain\Feedback\Event\FeedbackCreatedEvent;
use App\Domain\Feedback\Exception\BadFeedbackDataException;
use App\Domain\Feedback\UseCase\CreateFeedback;
use App\Domain\Feedback\Feedback;
use App\Domain\Feedback\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateFeedbackTest extends KernelTestCase
{
    public function testItValidatesEmail(): void
    {
        $storeFeedback = $this->getUseCase();

        $this->expectException(BadFeedbackDataException::class);
        ($storeFeedback)($this->getDto(['email' => 'invalid']));
    }

    public function testItValidatesName(): void
    {
        $storeFeedback = $this->getUseCase();

        $this->expectException(BadFeedbackDataException::class);
        ($storeFeedback)($this->getDto(['name' => '']));
    }

    public function testItValidatesMessage(): void
    {
        $storeFeedback = $this->getUseCase();

        $this->expectException(BadFeedbackDataException::class);
        ($storeFeedback)($this->getDto(['message' => '']));
    }

    public function testItValidatesPhoneNumber(): void
    {
        $storeFeedback = $this->getUseCase();

        $this->expectException(BadFeedbackDataException::class);
        ($storeFeedback)($this->getDto(['phoneNumber' => '']));
    }

    /**
     * @throws BadFeedbackDataException
     */
    public function testItStoresNewFeedback(): void
    {
        $storeFeedback = $this->getUseCase();

        $before = $this->countFeedbacks();

        $feedback = ($storeFeedback)($this->getDto());

        $this->assertNotNull($feedback->getId());
        $this->assertEquals($before + 1, $this->countFeedbacks());
    }

    /**
     * @throws BadFeedbackDataException
     */
    public function testItDispatchesEvent(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(fn($event) => $event instanceof FeedbackCreatedEvent),
                $this->callback(fn($name) => $name === FeedbackCreatedEvent::NAME),
            );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->method('denormalize')->willReturn(new Feedback);

        $createFeedback = new CreateFeedback(
            $this->createMock(ValidatorInterface::class),
            $this->createMock(EntityManagerInterface::class),
            $denormalizer,
            $dispatcher
        );

        ($createFeedback)($this->getDto());
    }

    private function getUseCase(): CreateFeedback
    {
        try {
            return self::getContainer()->get(CreateFeedback::class);
        } catch (Exception $e) {
            throw new LogicException('Service not found.', previous: $e);
        }
    }

    private function getDto(array $attributes = []): CreateFeedbackDto
    {
        $data = new CreateFeedbackDto(
            'foo@bar.com',
            'Name',
            'Message',
            '+6 (123) 777 12 34'
        );

        foreach ($attributes as $key => $value) {
            if (property_exists($data, $key)) {
                $data->$key = $value;
            }
        }

        return $data;
    }

    private function countFeedbacks(): int
    {
        return $this->getRepository()->count([]);
    }

    private function getRepository(): FeedbackRepository
    {
        try {
            return self::getContainer()->get(FeedbackRepository::class);
        } catch (Exception $e) {
            throw new LogicException('Class ' . FeedbackRepository::class . ' is inaccessible.', previous: $e);
        }
    }
}
