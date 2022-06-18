<?php

namespace App\Service\BookData\Provider;

use App\Domain\Book\Dto\NewBookDto;
use App\Service\BookData\Event\BookDataStreamedEvent;
use Generator;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use StdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

class GitLabBookDataProvider implements BookDataProviderInterface
{
    private string $url;
    private HttpClientInterface $http;
    private DenormalizerInterface $denormalizer;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        string                   $url,
        HttpClientInterface      $httpClient,
        DenormalizerInterface    $denormalizer,
        EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->url = $url;
        $this->http = $httpClient;
        $this->denormalizer = $denormalizer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): Traversable
    {
        try {
            foreach ($this->streamBookData() as $data) {
                yield $this->denormalizer->denormalize($data, NewBookDto::class);
            }
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('API is unreachable.', previous: $e);
        } catch (InvalidArgumentException|ExceptionInterface $e) {
            throw new RuntimeException('API has changed.', previous: $e);
        }
    }

    /**
     * @return Generator<StdClass>
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    protected function streamBookData(): Generator
    {
        $chunks = (function () {
            $response = $this->http->request(Request::METHOD_GET, $this->url);
            foreach ($this->http->stream($response) as $chunk) {
                yield $chunk->getContent();
            }
        })();

        foreach (Items::fromIterable($chunks) as $data) {
            $this->eventDispatcher->dispatch(new BookDataStreamedEvent($data), BookDataStreamedEvent::NAME);
            yield $data;
        }
    }
}
