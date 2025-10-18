<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit\Connection;

use Magento\Framework\Stomp\Connection\Factory;
use Magento\Framework\Stomp\Connection\FactoryOptions;
use PHPUnit\Framework\TestCase;
use Stomp\Network\Connection;

class FactoryTest extends TestCase
{
    /** @var FactoryOptions|\PHPUnit\Framework\MockObject\MockObject */
    private FactoryOptions $options;

    protected function setUp(): void
    {
        $this->options = $this->createMock(FactoryOptions::class);
    }

    public function testCreateWithSsl(): void
    {
        $this->runCreateTest(
            isSsl: true,
            host: 'localhost',
            port: '61614',
            sslOptions: [
                'verify_peer' => false,
                'allow_self_signed' => true
            ]
        );
    }

    public function testCreateWithoutSsl(): void
    {
        $this->runCreateTest(
            isSsl: false,
            host: '127.0.0.1',
            port: '61613'
        );
    }

    private function runCreateTest(bool $isSsl, string $host, string $port, array $sslOptions = []): void
    {
        $this->options->method('isSslEnabled')->willReturn($isSsl);
        $this->options->method('getHost')->willReturn($host);
        $this->options->method('getPort')->willReturn($port);

        if ($isSsl) {
            $this->options->method('getSslOptions')->willReturn($sslOptions);
        }

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects($this->once())->method('connect');

        $factory = new class($connectionMock) extends Factory {
            /** @var Connection */
            private Connection $connection;

            public function __construct(Connection $connection)
            {
                $this->connection = $connection;
            }

            protected function createConnectionInstance(
                string $broker,
                int $timeout = 1,
                array $context = []
            ): Connection {
                return $this->connection;
            }
        };

        $connection = $factory->create($this->options);
        $this->assertInstanceOf(Connection::class, $connection);
    }
}
