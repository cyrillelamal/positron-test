<?php

namespace App\Service\BookData\Provider;

use App\Domain\Book\Dto\CreateBookDto;
use DateTime;
use Exception;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

class GitLabBookDataProvider implements BookDataProviderInterface
{
    private string $url;
    private HttpClientInterface $http;

    public function __construct(
        string              $url,
        HttpClientInterface $httpClient,
    )
    {
        $this->url = $url;
        $this->http = $httpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): Traversable
    {
        try {
            $response = $this->http->request(Request::METHOD_GET, $this->url);

            $chunks = (function () use ($response) {
                foreach ($this->http->stream($response) as $chunk) {
                    yield $chunk->getContent();
                }
            })();

            foreach (Items::fromIterable($chunks) as $data) {
                yield $this->newCreateBookDto($data);
            }
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('API is unreachable.', previous: $e);
        } catch (InvalidArgumentException|Exception $e) {
            throw new RuntimeException('API has changed', previous: $e);
        }

        yield from [];
    }

    /**
     * @throws Exception
     */
    protected function newCreateBookDto(object $data): CreateBookDto
    {
        $dto = new CreateBookDto();

        foreach ($data as $property => $value) {
            if (property_exists($dto, $property)) {

                if ('publishedDate' === $property) {
                    $value = new DateTime($value->{'$date'});
                }

                $dto->$property = $value;
            }
        }

        return $dto;
    }
}
