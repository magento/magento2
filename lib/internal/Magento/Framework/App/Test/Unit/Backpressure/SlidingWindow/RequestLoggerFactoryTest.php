<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactory;
use Magento\Framework\App\Backpressure\SlidingWindow\ValkeyRequestLogger;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RequestLoggerFactory
 *
 * @covers \Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactory
 */
class RequestLoggerFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private MockObject $objectManagerMock;

    /**
     * @var RequestLoggerFactory
     */
    private RequestLoggerFactory $sut;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->sut = new RequestLoggerFactory(
            $this->objectManagerMock,
            [
                RedisRequestLogger::BACKPRESSURE_LOGGER_REDIS => RedisRequestLogger::class,
                RedisRequestLogger::BACKPRESSURE_LOGGER_VALKEY => ValkeyRequestLogger::class,
            ]
        );
    }

    /**
     * Test that create returns a RedisRequestLogger instance when redis type is provided
     *
     * @covers \Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactory::create()
     * @return void
     */
    public function testCreateShouldReturnRedisRequestLoggerWhenRedisTypeIsProvided(): void
    {
        $redisLoggerMock = $this->createStub(RedisRequestLogger::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(RedisRequestLogger::class)
            ->willReturn($redisLoggerMock);

        $result = $this->sut->create(RedisRequestLogger::BACKPRESSURE_LOGGER_REDIS);

        $this->assertSame($redisLoggerMock, $result);
    }

    /**
     * Test that create returns a ValkeyRequestLogger instance when valkey type is provided
     *
     * @covers \Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactory::create()
     * @return void
     */
    public function testCreateShouldReturnValkeyRequestLoggerWhenValkeyTypeIsProvided(): void
    {
        $valkeyLoggerMock = $this->createStub(ValkeyRequestLogger::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(ValkeyRequestLogger::class)
            ->willReturn($valkeyLoggerMock);

        $result = $this->sut->create(RedisRequestLogger::BACKPRESSURE_LOGGER_VALKEY);

        $this->assertSame($valkeyLoggerMock, $result);
    }

    /**
     * Test that create throws RuntimeException when an invalid type is provided
     *
     * @covers \Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactory::create()
     * @return void
     */
    public function testCreateShouldThrowRuntimeExceptionWhenInvalidTypeIsProvided(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            (string) __('Invalid request logger type: %1', 'invalid_type')
        );

        $this->objectManagerMock->expects($this->never())
            ->method('create');

        $this->sut->create('invalid_type');
    }
}
