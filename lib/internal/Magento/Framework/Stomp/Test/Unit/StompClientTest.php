<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit;

use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\Connection\ClientFactory;
use Magento\Framework\Stomp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Stomp\Connection\FactoryOptions;
use Magento\Framework\Stomp\Exception\ClientException;
use Magento\Framework\Stomp\StompClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stomp\Exception\ConnectionException;
use Stomp\Network\Connection;
use Stomp\Transport\Frame;

class StompClientTest extends TestCase
{
    #[DataProvider('declarationProvider')]
    public function testCreateQueue(?string $failedCommand, bool $sslEnabled): void
    {
        $connected = false;
        $response = null;
        $frames = [];
        $failure = new ConnectionException('Declaration connection failed');
        $connection = $this->createConnection($connected, $frames, $response, $failedCommand, $failure);
        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('getConnection')->willReturn($connection);
        $sslOptions = $sslEnabled ? ['verify_peer' => true] : null;
        $config->method('getValue')->willReturnMap([
            [Config::HOST, 'broker.example'],
            [Config::PORT, '61613'],
            [Config::SSL, $sslEnabled ? 'true' : 'false'],
            [Config::SSL_OPTIONS, $sslOptions],
        ]);
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $connectionFactory->expects($this->once())->method('create')->with(
            $this->callback(static function (FactoryOptions $options) use ($sslEnabled, $sslOptions): bool {
                return $options->getHost() === 'broker.example'
                    && $options->getPort() === '61613'
                    && $options->isSslEnabled() === $sslEnabled
                    && $options->getSslOptions() === $sslOptions;
            })
        )->willReturn($connection);
        $client = new StompClient($config, [], new ClientFactory($connectionFactory));
        $frames = [];

        try {
            $client->createQueue('queue.test');
            self::assertNull($failedCommand);
        } catch (ClientException $exception) {
            self::assertNotNull($failedCommand);
            self::assertSame($failure, $exception->getPrevious());
            self::assertSame("Failed to create 'queue.test' queue.", $exception->getMessage());
        }

        self::assertFalse($connected);
        self::assertSame('CONNECT', $frames[0]->getCommand());
        self::assertSame('SUBSCRIBE', $frames[1]->getCommand());
        $headers = $frames[1]->getHeaders();
        self::assertSame('queue.test', $headers['destination']);
        self::assertSame('ANYCAST', $headers['subscription-type']);
        self::assertSame('client-individual', $headers['ack']);
        self::assertSame('1 = 0', $headers['selector']);
        self::assertArrayHasKey('receipt', $headers);
        if ($failedCommand !== 'SUBSCRIBE') {
            self::assertSame('UNSUBSCRIBE', $frames[2]->getCommand());
            self::assertSame($headers['id'], $frames[2]['id']);
            self::assertArrayHasKey('receipt', $frames[2]->getHeaders());
        }
        self::assertSame('DISCONNECT', $frames[array_key_last($frames)]->getCommand());
        self::assertSame($failedCommand === 'SUBSCRIBE' ? 3 : 4, count($frames));
    }

    /**
     * @param bool $connected
     * @param array $frames
     * @param Frame|null $response
     * @param string|null $failedCommand
     * @param ConnectionException $failure
     * @return Connection
     */
    private function createConnection(
        bool &$connected,
        array &$frames,
        ?Frame &$response,
        ?string $failedCommand,
        ConnectionException $failure
    ): Connection {
        $connection = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs(['tcp://localhost:61613'])
            ->onlyMethods(['connect', 'disconnect', 'isConnected', 'writeFrame', 'readFrame'])
            ->getMock();
        $connection->method('connect')->willReturnCallback(
            static function () use (&$connected): void {
                $connected = true;
            }
        );
        $connection->method('disconnect')->willReturnCallback(
            static function () use (&$connected): void {
                $connected = false;
            }
        );
        $connection->method('isConnected')->willReturnCallback(
            static function () use (&$connected): bool {
                return $connected;
            }
        );
        $connection->method('writeFrame')->willReturnCallback(
            static function (Frame $frame) use (&$frames, &$response, $failedCommand, $failure): bool {
                $frames[] = $frame;
                if ($frame->getCommand() === $failedCommand) {
                    throw $failure;
                }
                $response = $frame->getCommand() === 'CONNECT'
                    ? new Frame('CONNECTED', [
                        'version' => '1.2',
                        'session' => 'test-session',
                        'server' => 'ActiveMQ-Artemis/2.40.0',
                    ])
                    : new Frame('RECEIPT', ['receipt-id' => $frame['receipt']]);
                return true;
            }
        );
        $connection->method('readFrame')->willReturnCallback(
            static function () use (&$response): ?Frame {
                return $response;
            }
        );
        return $connection;
    }

    /**
     * @return array
     */
    public static function declarationProvider(): array
    {
        return [
            'successful declaration' => [null, false],
            'successful TLS declaration' => [null, true],
            'failed subscription' => ['SUBSCRIBE', false],
            'failed unsubscription' => ['UNSUBSCRIBE', false],
        ];
    }
}
