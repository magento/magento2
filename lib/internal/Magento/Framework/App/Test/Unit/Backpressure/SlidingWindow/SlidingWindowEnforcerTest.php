<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\BackpressureExceededException;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactoryInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\SlidingWindowEnforcer;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SlidingWindowEnforcerTest extends TestCase
{
    /**
     * @var RequestLoggerFactoryInterface|MockObject
     */
    private RequestLoggerFactoryInterface $requestLoggerFactoryMock;

    /**
     * @var RequestLoggerInterface|MockObject
     */
    private $requestLoggerMock;

    /**
     * @var LimitConfigManagerInterface|MockObject
     */
    private $limitConfigManagerMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $loggerMock;

    /**
     * @var SlidingWindowEnforcer
     */
    private SlidingWindowEnforcer $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->requestLoggerMock = $this->createMock(RequestLoggerInterface::class);
        $this->requestLoggerFactoryMock = $this->createMock(RequestLoggerFactoryInterface::class);
        $this->limitConfigManagerMock = $this->createMock(LimitConfigManagerInterface::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $deploymentConfigMock->method('get')
            ->with('backpressure/logger/type')
            ->willReturn('someRequestType');
        $this->requestLoggerFactoryMock->method('create')
            ->with('someRequestType')
            ->willReturn($this->requestLoggerMock);

        $this->model = new SlidingWindowEnforcer(
            $this->requestLoggerFactoryMock,
            $this->limitConfigManagerMock,
            $this->dateTimeMock,
            $deploymentConfigMock,
            $this->loggerMock
        );
    }

    /**
     * Verify no exception when under limit with no previous record.
     *
     * @return void
     */
    public function testEnforcingUnderLimitPasses(): void
    {
        $time = time();
        $limitPeriod = 60;
        $limit = 1000;
        $curSlot = $time - ($time % $limitPeriod);
        $prevSlot = $curSlot - $limitPeriod;

        $this->dateTimeMock->method('gmtTimestamp')->willReturn($time);

        $this->initConfigMock($limit, $limitPeriod);

        $this->requestLoggerMock->method('incrAndGetFor')
            ->willReturnCallback(
                function (...$args) use ($curSlot, $limitPeriod, $limit) {
                    $this->assertEquals($curSlot, $args[1]);
                    $this->assertGreaterThan($limitPeriod, $args[2]);

                    return ((int)$limit / 2);
                }
            );
        $this->requestLoggerMock->method('getFor')
            ->willReturnCallback(
                function (...$args) use ($prevSlot) {
                    $this->assertEquals($prevSlot, $args[1]);

                    return null;
                }
            );

        $this->model->enforce($this->createContext());
    }

    /**
     * Cases for sliding window algo test.
     *
     * @return array
     */
    public function getSlidingCases(): array
    {
        return [
            'prev-lt-50%' => [999, false],
            'prev-eq-50%' => [1000, false],
            'prev-gt-50%' => [1001, true]
        ];
    }

    /**
     * Verify that sliding window algo works.
     *
     * @param int $prevCounter
     * @param bool $expectException
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     * @dataProvider getSlidingCases
     */
    public function testEnforcingSlided(int $prevCounter, bool $expectException): void
    {
        $limitPeriod = 60;
        $limit = 1000;
        $time = time();
        $curSlot = $time - ($time % $limitPeriod);
        $prevSlot = $curSlot - $limitPeriod;
        //50% of the period passed
        $time = $curSlot + ((int)($limitPeriod / 2));
        $this->dateTimeMock->method('gmtTimestamp')->willReturn($time);

        $this->initConfigMock($limit, $limitPeriod);

        $this->requestLoggerMock->method('incrAndGetFor')
            ->willReturnCallback(
                function () use ($limit) {
                    return ((int)$limit / 2);
                }
            );
        $this->requestLoggerMock->method('getFor')
            ->willReturnCallback(
                function (...$args) use ($prevCounter, $prevSlot) {
                    $this->assertEquals($prevSlot, $args[1]);

                    return $prevCounter;
                }
            );

        if ($expectException) {
            $this->expectException(BackpressureExceededException::class);
        }

        $this->model->enforce($this->createContext());
    }

    /**
     * Create context instance for tests.
     *
     * @return ContextInterface
     */
    private function createContext(): ContextInterface
    {
        $mock = $this->createMock(ContextInterface::class);
        $mock->method('getRequest')->willReturn($this->createMock(RequestInterface::class));
        $mock->method('getIdentity')->willReturn('127.0.0.1');
        $mock->method('getIdentityType')->willReturn(ContextInterface::IDENTITY_TYPE_IP);
        $mock->method('getTypeId')->willReturn('test');

        return $mock;
    }

    /**
     * Initialize config reader mock.
     *
     * @param int $limit
     * @param int $limitPeriod
     * @return void
     */
    private function initConfigMock(int $limit, int $limitPeriod): void
    {
        $configMock = $this->createMock(LimitConfig::class);
        $configMock->method('getPeriod')->willReturn($limitPeriod);
        $configMock->method('getLimit')->willReturn($limit);
        $this->limitConfigManagerMock->method('readLimit')->willReturn($configMock);
    }

    /**
     * Invalid type of request logger
     */
    public function testRequestLoggerTypeIsInvalid()
    {
        $this->requestLoggerFactoryMock->method('create')
            ->with('wrong-type')
            ->willThrowException(new RuntimeException(__('Invalid request logger type: %1', 'wrong-type')));
        $this->loggerMock->method('error')
            ->with('Invalid request logger type: %1', 'wrong-type');
    }
}
