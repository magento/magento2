<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Backpressure;

use Magento\Framework\App\Backpressure\BackpressureExceededException;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\SlidingWindowEnforcer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SlidingWindowEnforcerTest extends TestCase
{
    /**
     * @var RequestLoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var LimitConfigManagerInterface|MockObject
     */
    private $config;

    /**
     * @var DateTime|MockObject
     */
    private $dateTime;

    private SlidingWindowEnforcer $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(RequestLoggerInterface::class);
        $this->config = $this->createMock(LimitConfigManagerInterface::class);
        $this->dateTime = $this->createMock(DateTime::class);

        $this->model = new SlidingWindowEnforcer($this->logger, $this->config, $this->dateTime);
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

        $this->dateTime->method('gmtTimestamp')->willReturn($time);

        $this->initConfigMock($limit, $limitPeriod);

        $this->logger->method('incrAndGetFor')
            ->willReturnCallback(
                function (ContextInterface $context, int $slot, int $expireAfter) use ($curSlot, $limitPeriod, $limit) {
                    $this->assertEquals($curSlot, $slot);
                    $this->assertGreaterThan($limitPeriod, $expireAfter);

                    return ((int) $limit / 2);
                }
            );
        $this->logger->method('getFor')
            ->willReturnCallback(
                function (ContextInterface $context, int $slot) use ($prevSlot) {
                    $this->assertEquals($prevSlot, $slot);

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
        $time = $curSlot + ((int) ($limitPeriod / 2));
        $this->dateTime->method('gmtTimestamp')->willReturn($time);

        $this->initConfigMock($limit, $limitPeriod);

        $this->logger->method('incrAndGetFor')
            ->willReturnCallback(
                function (ContextInterface $context, int $slot, int $expireAfter) use ($limit) {
                    return ((int) $limit / 2);
                }
            );
        $this->logger->method('getFor')
            ->willReturnCallback(
                function (ContextInterface $context, int $slot) use ($prevCounter, $prevSlot) {
                    $this->assertEquals($prevSlot, $slot);

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
        $this->config->method('readLimit')->willReturn($configMock);
    }
}
