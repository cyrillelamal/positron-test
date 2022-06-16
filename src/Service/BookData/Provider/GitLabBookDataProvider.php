<?php

namespace App\Service\BookData\Provider;

use App\Service\BookData\Dto\BookDataDto;
use App\Service\File\TmpFile;
use Exception;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

class GitLabBookDataProvider implements BookDataProviderInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

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
        $swap = new TmpFile();

        try {
            $response = $this->http->request(Request::METHOD_GET, $this->url);
            $this->saveResponseContentToSwapFile($response, $swap);

            foreach (Items::fromFile($swap->getTmpfname()) as $data) {
                yield BookDataDto::hydrateFrom((array)$data);
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('API is unreachable', ['exception' => $e, 'http' => $this->http]);
        } catch (InvalidArgumentException|Exception $e) {
            $this->logger->error('API has changed', ['exception' => $e, 'http' => $this->http]);
        } finally {
            $swap->close();
        }

        yield from [];
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function saveResponseContentToSwapFile($response, TmpFile $swap): void
    {
        foreach ($this->http->stream($response) as $chunk) {
            $swap->write($chunk->getContent());
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
