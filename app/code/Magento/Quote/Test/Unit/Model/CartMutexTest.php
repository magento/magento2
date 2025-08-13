<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Lock\LockManagerInterface;
use Magento\Quote\Model\CartLockedException;
use Magento\Quote\Model\CartMutex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CartMutexTest extends TestCase
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
     * @var CartMutex
     */
    private $cartMutex;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cartMutex = new CartMutex($this->lockManager, $this->logger);
    }

    /**
     * Tests cart mutex execution with different callables.
     *
     * @param callable|string $callable
     * @param array $args
     * @param mixed $expectedResult
     * @return void
     * @dataProvider callableDataProvider
     */
    public function testSuccessfulExecution(callable|string $callable, array $args, $expectedResult): void
    {
        if ($callable === 'privateMethod') {
            $callable = \Closure::fromCallable([$this, 'privateMethod']);
        }

        $cartId = 1;
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with($this->stringContains((string)$cartId))
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with($this->stringContains((string)$cartId));

        $result = $this->cartMutex->execute($cartId, $callable, $args);

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
            ['callable' => $functionWithArgs, 'args' => [1,2], 'expectedResult' => 3],
            [
                'callable' => 'privateMethod',
                'args' => ['test'],
                'expectedResult' => 'test'
            ],
        ];
    }

    /**
     * Tests exception when cart is being processed and locked.
     *
     * @return void
     */
    public function testCartIsLocked(): void
    {
        $cartId = 1;
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with($this->stringContains((string)$cartId))
            ->willReturn(false);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->stringContains((string)$cartId));
        $this->lockManager->expects($this->never())
            ->method('unlock');
        $this->expectException(CartLockedException::class);
        $callable = function () {
        };

        $this->cartMutex->execute($cartId, $callable);
    }

    /**
     * Private method for data provider.
     *
     * @param string $var
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function privateMethod(string $var)
    {
        return $var;
    }
}
