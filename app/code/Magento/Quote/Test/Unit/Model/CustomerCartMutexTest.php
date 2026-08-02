<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Lock\LockManagerInterface;
use Magento\Quote\Model\CustomerCartMutex;
use Magento\Quote\Model\CustomerCartMutexException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CustomerCartMutexTest extends TestCase
{
    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;
    
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var CustomerCartMutex
     */
    private $customerCartMutex;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->customerCartMutex = new CustomerCartMutex(
            $this->lockManager,
            $this->logger,
            $this->storeManager
        );
    }

    /**
     * Tests customer cart mutex execution with different callables.
     *
     * @param callable|string $callable
     * @param array $args
     * @param mixed $expectedResult
     * @return void
     */
    #[DataProvider('callableDataProvider')]
    public function testSuccessfulExecution(callable|string $callable, array $args, $expectedResult): void
    {
        $customerId = 1;
        $storeId = 1;
        $websiteId = 2;
        $lockName = 'customer_cart_' . $customerId . '_' . $websiteId;
        $this->storeManager->method('getStore')
            ->with($storeId)
            ->willReturn($this->createConfiguredMock(StoreInterface::class, ['getWebsiteId' => $websiteId]));
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with($lockName)
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with($lockName);

        $result = $this->customerCartMutex->execute($customerId, $storeId, $callable, $args);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array[]
     */
    public static function callableDataProvider(): array
    {
        $functionWithArgs = function (int $a, int $b) {
            return $a + $b;
        };

        $functionWithoutArgs = function () {
            return 'Function without args';
        };

        return [
            ['callable' => $functionWithoutArgs, 'args' => [], 'expectedResult' => 'Function without args'],
            ['callable' => $functionWithArgs, 'args' => [1, 2], 'expectedResult' => 3]
        ];
    }

    /**
     * Tests that CouldNotSaveException is thrown when the lock cannot be acquired.
     *
     * @return void
     */
    public function testLockCannotBeAcquired(): void
    {
        $customerId = 1;
        $storeId = 1;
        $websiteId = 2;
        $lockName = 'customer_cart_' . $customerId . '_' . $websiteId;
        $this->storeManager->method('getStore')
            ->with($storeId)
            ->willReturn($this->createConfiguredMock(StoreInterface::class, ['getWebsiteId' => $websiteId]));
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with($lockName)
            ->willReturn(false);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with('The customer cart is locked, the request has been aborted. Customer ID: 1, Store ID: 1');
        $this->lockManager->expects($this->never())
            ->method('unlock');
        $this->expectException(CustomerCartMutexException::class);
        $callable = function () {
        };

        $this->customerCartMutex->execute($customerId, $storeId, $callable);
    }
}
