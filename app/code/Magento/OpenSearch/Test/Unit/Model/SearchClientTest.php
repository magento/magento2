<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Test\Unit\Model;

use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;
use Magento\OpenSearch\Model\SearchClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for OpenSearch client configuration.
 */
class SearchClientTest extends TestCase
{
    /**
     * @return array
     */
    public static function buildConfigDataProvider(): array
    {
        return [
            'timeout is applied to all requests' => [
                ['hostname' => 'localhost', 'port' => '9200', 'timeout' => '15'],
                'http://localhost:9200',
                ['client' => ['timeout' => 15]],
            ],
            'https host' => [
                ['hostname' => 'https://localhost', 'port' => '9200', 'timeout' => 5],
                'https://localhost:9200',
                ['client' => ['timeout' => 5]],
            ],
            'no timeout configured' => [
                ['hostname' => 'localhost', 'port' => '9200'],
                'http://localhost:9200',
                null,
            ],
        ];
    }

    /**
     * Test that the client configuration contains the host and the configured request timeout.
     *
     * @param array $options
     * @param string $expectedHost
     * @param array|null $expectedConnectionParams
     */
    #[DataProvider('buildConfigDataProvider')]
    public function testBuildConfig(array $options, string $expectedHost, ?array $expectedConnectionParams): void
    {
        $client = new SearchClient($options, null, [], $this->createMock(DynamicTemplatesProvider::class));
        $method = new \ReflectionMethod(SearchClient::class, 'buildOSConfig');

        $config = $method->invoke($client, $options);

        $this->assertSame([$expectedHost], $config['hosts']);
        $this->assertSame($expectedConnectionParams, $config['connectionParams'] ?? null);
    }
}
