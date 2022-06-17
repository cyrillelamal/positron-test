<?php

namespace App\Domain\Book\UseCase;

use App\Domain\Author\Dto\CreateAuthorDto;
use App\Domain\Author\UseCase\FindAuthorsByName;
use App\Domain\Author\UseCase\InsertNewAuthors;
use App\Domain\Book\Dto\CreateBookDto;
use App\Domain\Category\Dto\CreateCategoryDto;
use App\Domain\Category\UseCase\FindCategoriesByNames;
use App\Domain\Category\UseCase\InsertNewCategories;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Repository\BookRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

class InsertNewBooks implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private EntityManagerInterface $entityManager;
    private FindCategoriesByNames $findCategoriesByNames;
    private FindAuthorsByName $findAuthorsByName;
    private InsertNewCategories $insertNewCategories;
    private InsertNewAuthors $insertNewAuthors;
    private BookRepository $repository;
    private FilesystemOperator $storage;
    private HttpClientInterface $http;
    private string $directory;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindCategoriesByNames  $findCategoriesByNames,
        FindAuthorsByName      $findAuthorsByName,
        InsertNewCategories    $insertNewCategories,
        InsertNewAuthors       $insertNewAuthors,
        BookRepository         $repository,
        FilesystemOperator     $booksImagesStorage,
        HttpClientInterface    $http,
        string                 $directory,
    )
    {
        $this->entityManager = $entityManager;
        $this->findCategoriesByNames = $findCategoriesByNames;
        $this->findAuthorsByName = $findAuthorsByName;
        $this->insertNewCategories = $insertNewCategories;
        $this->insertNewAuthors = $insertNewAuthors;
        $this->repository = $repository;
        $this->storage = $booksImagesStorage;
        $this->http = $http;
        $this->directory = $directory;
    }

    /**
     * @param Traversable<CreateBookDto> $data
     * @return Book[] the newly created books.
     * @throws Exception
     */
    public function __invoke(Traversable $data): array
    {
        $books = [];
        $this->entityManager->beginTransaction();
        try {
            foreach ($data as $bookData) /** @var CreateBookDto $bookData */ {
                if ($this->repository->similarBookExists($bookData->title, $bookData->isbn)) {
                    continue;
                }

                $this->logger->debug('Making book', ['data' => $bookData]);
                $book = $this->makeBook($bookData);
                $this->entityManager->persist($book);
                $books[] = $book;

                $this->entityManager->flush(); // FIXME: N + 1, e.g. with chunks.
            }
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->logger->error('Cannot insert new books', ['exception' => $e]);
            $this->entityManager->rollback();
            throw $e;
        }

        return $books;
    }

    protected function insertNewCategories(string ...$categories): void
    {
        $possiblyNewCategories = array_map(fn(string $name) => new CreateCategoryDto($name), $categories);

        ($this->insertNewCategories)(...$possiblyNewCategories);
    }

    /**
     * @param string ...$categories
     * @return Category[]
     */
    protected function reloadCategories(string ...$categories): array
    {
        return ($this->findCategoriesByNames)(...$categories);
    }

    protected function insertNewAuthors(string ...$authors): void
    {
        $possiblyNewAuthors = array_map(fn(string $name) => new CreateAuthorDto($name), $authors);

        ($this->insertNewAuthors)(...$possiblyNewAuthors);
    }

    /**
     * @param string ...$authors
     * @return Author[]
     */
    protected function reloadAuthors(string ...$authors): array
    {
        return ($this->findAuthorsByName)(...$authors);
    }

    protected function makeBook(CreateBookDto $dto): Book
    {
        $this->insertNewCategories(...$dto->categories);
        $this->insertNewAuthors(...$dto->authors);

        $categories = $this->reloadCategories(...$dto->categories);
        $authors = $this->reloadAuthors(...$dto->authors);

        $book = new Book();

        $book->setTitle($dto->title);
        $book->setIsbn($dto->isbn);
        $book->setPageCount($dto->pageCount);
        $book->setShortDescription($dto->shortDescription);
        $book->setLongDescription($dto->longDescription);
        $book->setStatus($dto->status);
        $book->setUpdatedAt(new DateTime());
        $book->setPublishedDate($dto->publishedDate);

        foreach ($categories as $category) {
            $book->addCategory($category);
        }

        foreach ($authors as $author) {
            $book->addAuthor($author);
        }

        if ($dto->thumbnailUrl) {
            $file = $this->storeThumbNail($dto);
            $book->setThumbnailFile($file);
        }

        return $book;
    }

    protected function storeThumbNail(CreateBookDto $dto): UploadedFile
    {
        try {
            $response = $this->http->request(Request::METHOD_GET, $dto->thumbnailUrl);

            $basename = pathinfo($dto->thumbnailUrl, PATHINFO_BASENAME);
            $location = Uuid::v4() . $basename;

            $this->storage->write("/$location", $response->getContent());
        } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
            $this->logger->error('Thumb file is unreachable.', ['url' => $dto->thumbnailUrl, 'exception' => $e]);
            throw new RuntimeException('Thumb file is unreachable.', previous: $e);
        } catch (FilesystemException $e) {
            $this->logger->error('Cannot store file', ['storage' => $this->storage, 'exception' => $e]);
            throw new RuntimeException('Cannot store file.', previous: $e);
        }

        return new UploadedFile($this->directory . "/$location", $basename, test: true);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
