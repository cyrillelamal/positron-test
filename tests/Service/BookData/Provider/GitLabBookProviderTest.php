<?php

namespace App\Tests\Service\BookData\Provider;

use App\Service\BookData\Provider\GitLabBookDataProvider;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitLabBookProviderTest extends KernelTestCase
{
    public const URL = 'http://foo.test';

    public function testItTouchesTheApiUrl(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->expects($this->once())
            ->method('request')
            ->with(Request::METHOD_GET, self::URL);

        $provider = new GitLabBookDataProvider(self::URL, $http);

        $this->expectException(RuntimeException::class); // Because of empty JSON.

        iterator_to_array($provider->getData());
    }
}
