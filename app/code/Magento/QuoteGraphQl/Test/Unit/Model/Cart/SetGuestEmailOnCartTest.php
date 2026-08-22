<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\SetGuestEmailOnCart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetGuestEmailOnCartTest extends TestCase
{
    /**
     * @var SetGuestEmailOnCart
     */
    private SetGuestEmailOnCart $model;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private CartRepositoryInterface|MockObject $cartRepositoryMock;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $contextMock;

    /**
     * @var CartInterface|MockObject
     */
    private CartInterface|MockObject $cartMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->cartMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods(['setCustomerEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SetGuestEmailOnCart($this->cartRepositoryMock);
    }

    /**
     * Test that email is successfully set on cart
     *
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteSuccessfully(): void
    {
        $email = 'guest@example.com';

        $this->cartMock->expects($this->once())
            ->method('setCustomerEmail')
            ->with($email);

        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock);

        $this->model->execute($this->contextMock, $this->cartMock, $email);
    }

    /**
     * Test that CouldNotSaveException is converted to LocalizedException
     *
     * @return void
     */
    public function testExecuteThrowsLocalizedExceptionWhenSaveFails(): void
    {
        $email = 'guest@example.com';
        $originalMessage = 'Could not save cart';
        $couldNotSaveException = new CouldNotSaveException(__($originalMessage));

        $this->cartMock->expects($this->once())
            ->method('setCustomerEmail')
            ->with($email);

        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock)
            ->willThrowException($couldNotSaveException);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($originalMessage);

        $this->model->execute($this->contextMock, $this->cartMock, $email);
    }
}
