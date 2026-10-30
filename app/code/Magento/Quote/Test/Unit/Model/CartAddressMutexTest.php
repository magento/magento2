<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Lock\LockManagerInterface;
use Magento\Quote\Model\CartAddressMutex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartAddressMutexTest extends TestCase
{
    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    /**
     * @var CartAddressMutex
     */
    private $cartAddressMutex;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->cartAddressMutex = new CartAddressMutex(
            $this->lockManager
        );
    }

    /**
     * Tests when cart address is being processed and locked.
     *
     * @return void
     */
    public function testCartAddressIsLocked(): void
    {
        $addressId = $result = 1;
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->willReturn(false);

        $expectedResult = $this->cartAddressMutex->execute(
            'cart_billing_address_lock_'.$addressId,
            \Closure::fromCallable([$this, 'privateMethod']),
            $result,
            ['1']
        );
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Tests when cart address is being locked.
     *
     * @return void
     */
    public function testCartAddressUnLocked(): void
    {
        $addressId = $result = 1;
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->willReturn(true);

        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with($this->stringContains((string)$addressId));

        $expectedResult = $this->cartAddressMutex->execute(
            'cart_billing_address_lock_'.$addressId,
            \Closure::fromCallable([$this, 'privateMethod']),
            $result,
            ['1']
        );
        $this->assertEquals($result, $expectedResult);
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
