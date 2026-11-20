<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Amqp\Test\Unit\Setup;

use Magento\Amqp\Setup\ConnectionValidator;
use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\FactoryOptions;
use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionValidatorTest extends TestCase
{
    private const TEST_HOST = 'localhost';
    private const TEST_PORT = '5672';
    private const TEST_USER = 'guest';
    private const TEST_PASSWORD = 'guest';
    private const TEST_VHOST = '/';
    private const TEST_SSL_PORT = '5671';
    private const TEST_INVALID_HOST = 'invalid-host';

    /**
     * @var ConnectionFactory|MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

    protected function setUp(): void
    {
        $this->connectionFactoryMock = $this->createMock(ConnectionFactory::class);
        $this->connectionValidator = new ConnectionValidator($this->connectionFactoryMock);
    }

    /**
     * Test that MINIMUM_RABBITMQ_VERSION constant is defined
     */
    public function testMinimumVersionConstant(): void
    {
        $this->assertEquals('4.3.0', ConnectionValidator::MINIMUM_RABBITMQ_VERSION);
    }

    /**
     * Test getServerVersion returns version from AMQP table format (array)
     */
    public function testGetServerVersionReturnsVersionFromAmqpTableFormat(): void
    {
        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())
            ->method('getServerProperties')
            ->willReturn(['version' => ['S', '4.3.1']]);
        $connectionMock->expects($this->once())
            ->method('close');

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(FactoryOptions::class))
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertEquals('4.3.1', $result);
    }

    /**
     * Test getServerVersion returns version from plain string format
     */
    public function testGetServerVersionReturnsVersionFromStringFormat(): void
    {
        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())
            ->method('getServerProperties')
            ->willReturn(['version' => '4.4.0']);
        $connectionMock->expects($this->once())
            ->method('close');

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertEquals('4.4.0', $result);
    }

    /**
     * Test getServerVersion returns null when version key is missing
     */
    public function testGetServerVersionReturnsNullWhenVersionMissing(): void
    {
        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())
            ->method('getServerProperties')
            ->willReturn(['product' => ['S', 'RabbitMQ']]);
        $connectionMock->expects($this->once())
            ->method('close');

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertNull($result);
    }

    /**
     * Test getServerVersion returns null when connection throws exception
     */
    public function testGetServerVersionReturnsNullOnConnectionFailure(): void
    {
        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Connection refused'));

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_INVALID_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertNull($result);
    }

    /**
     * Test getServerVersion returns null when getServerProperties throws exception
     */
    public function testGetServerVersionReturnsNullWhenPropertiesThrow(): void
    {
        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())
            ->method('getServerProperties')
            ->willThrowException(new \RuntimeException('Protocol error'));
        $connectionMock->expects($this->once())
            ->method('close');

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertNull($result);
    }

    /**
     * Test getServerVersion handles close() throwing exception gracefully
     */
    public function testGetServerVersionHandlesCloseException(): void
    {
        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())
            ->method('getServerProperties')
            ->willReturn(['version' => ['S', '4.3.0']]);
        $connectionMock->expects($this->once())
            ->method('close')
            ->willThrowException(new \Exception('Already closed'));

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertEquals('4.3.0', $result);
    }

    /**
     * Test getServerVersion passes SSL options correctly
     */
    public function testGetServerVersionWithSslOptions(): void
    {
        $sslOptions = ['verify_peer' => false];

        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())
            ->method('getServerProperties')
            ->willReturn(['version' => '4.3.0']);
        $connectionMock->expects($this->once())
            ->method('close');

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function (FactoryOptions $options) use ($sslOptions) {
                return $options->getHost() === self::TEST_HOST
                    && $options->getPort() === self::TEST_SSL_PORT
                    && $options->isSslEnabled() === true
                    && $options->getSslOptions() === $sslOptions;
            }))
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->getServerVersion(
            self::TEST_HOST,
            self::TEST_SSL_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST,
            true,
            $sslOptions
        );

        $this->assertEquals('4.3.0', $result);
    }

    /**
     * Test existing isConnectionValid still works correctly
     */
    public function testIsConnectionValidReturnsTrueOnSuccess(): void
    {
        $connectionMock = $this->createMock(AbstractConnection::class);
        $connectionMock->expects($this->once())->method('close');

        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $result = $this->connectionValidator->isConnectionValid(
            self::TEST_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertTrue($result);
    }

    /**
     * Test existing isConnectionValid returns false on failure
     */
    public function testIsConnectionValidReturnsFalseOnFailure(): void
    {
        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Connection refused'));

        $result = $this->connectionValidator->isConnectionValid(
            self::TEST_INVALID_HOST,
            self::TEST_PORT,
            self::TEST_USER,
            self::TEST_PASSWORD,
            self::TEST_VHOST
        );

        $this->assertFalse($result);
    }
}
