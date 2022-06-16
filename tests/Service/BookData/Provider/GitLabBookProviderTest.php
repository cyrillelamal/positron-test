<?php

namespace App\Tests\Service\BookData\Provider;

use App\Service\BookData\Provider\GitLabBookDataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitLabBookProviderTest extends TestCase
{
    public const URL = 'http://foo.test';

    public function testItTouchesTheApiUrl(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->expects($this->once())
            ->method('request')
            ->with(Request::METHOD_GET, self::URL);

        $provider = new GitLabBookDataProvider(self::URL, $http);
        $provider->setLogger($this->createMock(LoggerInterface::class));

        iterator_to_array($provider->getData()); // It's a generator.
    }
}
