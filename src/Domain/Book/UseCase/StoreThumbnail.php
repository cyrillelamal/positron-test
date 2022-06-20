<?php

namespace App\Domain\Book\UseCase;

use App\Domain\Book\Dto\StoreThumbnailDto;
use App\Domain\Book\Repository\BookRepository;
use Exception;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class StoreThumbnail implements MessageHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    private FilesystemOperator $storage;
    private BookRepository $repository;
    private HttpClientInterface $http;
    private string $directory;

    public function __construct(
        FilesystemOperator  $booksImagesStorage,
        BookRepository      $repository,
        HttpClientInterface $http,
        string              $thumbnailDirectory,
    )
    {
        $this->storage = $booksImagesStorage;
        $this->repository = $repository;
        $this->http = $http;
        $this->directory = $thumbnailDirectory;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(StoreThumbnailDto $dto): void
    {
        if (!$dto->url) {
            return;
        }

        $book = $this->repository->find($dto->bookId);

        if (!$book) {
            return;
        }

        [$file, $location] = $this->storeFile($dto->url);

        try {
            $book->setThumbnailFile($file);
            $this->repository->add($book, true);
        } catch (Exception $e) {
            $this->logger->error('Cannot update thumbnail.', ['file' => $file, 'location' => $location, 'exception' => $e]);
            $this->storage->delete($location);
            throw $e;
        }
    }

    /**
     * @param string $url
     * @return array{UploadedFile, string} file and its storage location
     */
    private function storeFile(string $url): array
    {
        $basename = pathinfo($url, PATHINFO_BASENAME);
        $location = Uuid::v4() . $basename;

        try {
            $response = $this->http->request(Request::METHOD_GET, $url);

            $this->storage->write("/$location", $response->getContent());

            return [new UploadedFile($this->directory . "/$location", $basename, test: true), $location];
        } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
            $this->logger->error('Thumbnail file is unreachable.', ['url' => $url, 'exception' => $e]);
            throw new RuntimeException('Thumb file is unreachable.', previous: $e);
        } catch (FilesystemException $e) {
            $this->logger->error('Cannot store file.', ['storage' => $this->storage, 'exception' => $e]);
            throw new RuntimeException('Cannot store file.', previous: $e);
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}