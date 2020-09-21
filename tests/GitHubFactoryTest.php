<?php

declare(strict_types=1);

/*
 * This file is part of Laravel GitHub.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\GitHub;

use Github\Client;
use GrahamCampbell\GitHub\Auth\AuthenticatorFactory;
use GrahamCampbell\GitHub\Cache\ConnectionFactory;
use GrahamCampbell\GitHub\GitHubFactory;
use GrahamCampbell\TestBench\AbstractTestCase as AbstractTestBenchTestCase;
use Http\Client\Common\HttpMethodsClient;
use Illuminate\Contracts\Cache\Factory;
use InvalidArgumentException;
use Mockery;
use Psr\Cache\CacheItemPoolInterface;

/**
 * This is the github factory test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class GitHubFactoryTest extends AbstractTestBenchTestCase
{
    public function testMakeStandard()
    {
        $factory = $this->getFactory();

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token']);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeStandardExplicitCache()
    {
        $factory = $this->getFactory();

        $factory[1]->shouldReceive('make')->once()->with(['name' => 'main', 'driver' => 'illuminate'])->andReturn(Mockery::mock(CacheItemPoolInterface::class));

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token', 'cache' => ['name' => 'main', 'driver' => 'illuminate']]);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeStandardNamedCache()
    {
        $factory = $this->getFactory();

        $factory[1]->shouldReceive('make')->once()->with(['name' => 'main', 'driver' => 'illuminate', 'connection' => 'foo'])->andReturn(Mockery::mock(CacheItemPoolInterface::class));

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token', 'cache' => ['name' => 'main', 'driver' => 'illuminate', 'connection' => 'foo']]);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeStandardNoCacheOrBackoff()
    {
        $factory = $this->getFactory();

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token', 'cache' => false, 'backoff' => false]);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeStandardExplicitBackoff()
    {
        $factory = $this->getFactory();

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token', 'backoff' => true]);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeStandardExplicitEnterprise()
    {
        $factory = $this->getFactory();

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token', 'enterprise' => 'https://example.com/']);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeStandardExplicitVersion()
    {
        $factory = $this->getFactory();

        $client = $factory[0]->make(['token' => 'your-token', 'method' => 'token', 'version' => 'v4']);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeNoneMethod()
    {
        $factory = $this->getFactory();

        $client = $factory[0]->make(['method' => 'none']);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpMethodsClient::class, $client->getHttpClient());
    }

    public function testMakeInvalidMethod()
    {
        $factory = $this->getFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported authentication method [bar].');

        $factory[0]->make(['method' => 'bar']);
    }

    public function testMakeEmpty()
    {
        $factory = $this->getFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The github factory requires an auth method.');

        $factory[0]->make([]);
    }

    protected function getFactory()
    {
        $cache = Mockery::mock(ConnectionFactory::class);

        return [new GitHubFactory(new AuthenticatorFactory(), $cache), $cache];
    }
}
