<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Test\Unit\Model;

use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;
use Magento\OpenSearch\Model\SearchClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SearchClientTest extends TestCase
{
    private const HANDLER_STOP = 'request captured';

    /** @var array|null */
    private ?array $capturedRequest = null;

    public function testQueryAppliesConfiguredTimeout(): void
    {
        $this->send(fn (SearchClient $client) => $client->query(['index' => 'magento2', 'body' => []]), 5);

        $this->assertSame(5, $this->capturedRequest['client']['timeout'] ?? null);
    }

    public function testBulkQueryAppliesConfiguredTimeout(): void
    {
        $this->send(fn (SearchClient $client) => $client->bulkQuery(['body' => [['index' => []], []]]), 5);

        $this->assertSame(5, $this->capturedRequest['client']['timeout'] ?? null);
    }

    public function testPerRequestTimeoutOverridesConfiguredTimeout(): void
    {
        $this->send(
            fn (SearchClient $client) => $client->query(['index' => 'magento2', 'client' => ['timeout' => 2]]),
            5
        );

        $this->assertSame(2, $this->capturedRequest['client']['timeout'] ?? null);
    }

    public function testNoTimeoutIsAppliedWhenNotConfigured(): void
    {
        $this->send(fn (SearchClient $client) => $client->query(['index' => 'magento2', 'body' => []]), 0);

        $this->assertNotNull($this->capturedRequest);
        $this->assertArrayNotHasKey('timeout', $this->capturedRequest['client'] ?? []);
    }

    private function send(callable $call, int $timeout): void
    {
        $client = new SearchClient(
            [
                'hostname' => 'localhost',
                'port' => '9200',
                'timeout' => $timeout,
                'index' => 'magento2',
                'enableAuth' => 0,
                'handler' => function (array $request): void {
                    $this->capturedRequest = $request;
                    throw new RuntimeException(self::HANDLER_STOP);
                },
            ],
            null,
            [],
            new DynamicTemplatesProvider([])
        );

        try {
            $call($client);
            $this->fail('The request handler was not invoked.');
        } catch (RuntimeException $e) {
            $this->assertSame(self::HANDLER_STOP, $e->getMessage());
        }
    }
}
