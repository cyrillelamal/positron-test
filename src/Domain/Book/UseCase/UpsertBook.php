<?php

namespace App\Domain\Book\UseCase;

use App\Domain\Book\Dto\AddAuthorsDto;
use App\Domain\Book\Dto\AddCategoriesDto;
use App\Domain\Book\Dto\NewBookDto;
use App\Domain\Book\Dto\StoreThumbnailDto;
use App\Domain\Book\Event\BookCreatedEvent;
use App\Domain\Book\Book;
use App\Domain\Book\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class UpsertBook implements MessageHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    private DenormalizerInterface $denormalizer;
    private BookRepository $repository;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DenormalizerInterface    $denormalizer,
        BookRepository           $repository,
        EntityManagerInterface   $entityManager,
        MessageBusInterface      $bus,
        EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->denormalizer = $denormalizer;
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws Exception
     */
    public function __invoke(NewBookDto $data): void
    {
        $book = $this->makeBook($data);

        try {
            $break = $this->entityManager->wrapInTransaction(function () use ($book): bool {
                $this->logger->debug('Looking for similar books.', ['book' => $book]);
                if ($this->repository->similarExists($book)) {
                    return true;
                }

                $this->entityManager->persist($book);
                $this->entityManager->flush();
                return false;
            });

            if ($break) {
                return; // damn closure
            }

            $this->eventDispatcher->dispatch(new BookCreatedEvent($book), BookCreatedEvent::NAME);

            $this->storeThumbnail($book, $data->thumbnailUrl);
            $this->addCategories($book, ...$data->categories);
            $this->addAuthors($book, ...$data->authors);
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('Cannot add book.', ['book' => $book, 'exception' => $e]);
            throw $e;
        }
    }

    private function makeBook(NewBookDto $data): Book
    {
        try {
            /** @var Book $book */
            $book = $this->denormalizer->denormalize($data, Book::class, context: [
                AbstractNormalizer::IGNORED_ATTRIBUTES => [
                    'publishedDate',
                    'authors',
                    'categories',
                ],
            ]);
        } catch (ExceptionInterface $e) {
            $this->logger->error('Cannot hydrate book entity.', ['data' => $data, 'exception' => $e]);
            throw new RuntimeException('Cannot hydrate book entity.');
        }

        $book->setPublishedDate($data->publishedDate);

        return $book;
    }

    private function storeThumbnail(Book $book, ?string $url): void
    {
        $this->bus->dispatch(new StoreThumbnailDto($book->getId(), $url));
    }

    private function addCategories(Book $book, string ...$categories): void
    {
        $dto = new AddCategoriesDto($book->getId(), $categories);

        $this->bus->dispatch($dto);
    }

    private function addAuthors(Book $book, string ...$authors): void
    {
        $dto = new AddAuthorsDto($book->getId(), $authors);

        $this->bus->dispatch($dto);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
