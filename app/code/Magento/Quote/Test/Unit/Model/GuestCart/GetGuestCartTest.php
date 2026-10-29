<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetGuestCartTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var GetGuestCart
     */
    private GetGuestCart $model;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepositoryMock;

    /**
     * Create Quote mock with getCustomerId method (magic method in Quote model).
     */
    private function createQuoteMock(): MockObject
    {
        return $this->createPartialMockWithReflection(Quote::class, ['getCustomerId']);
    }

    /**
     * @var string
     */
    private string $maskedCartId = 'f216207248d65c789b17be8545e0aa73';

    /**
     * @var int
     */
    private int $quoteId = 123;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->model = new GetGuestCart($this->cartRepositoryMock);
    }

    /**
     * Test execute returns quote when cart is a guest cart (customerId = 0).
     */
    public function testExecuteReturnsQuoteWhenGuestCart(): void
    {
        $quoteMock = $this->createQuoteMock();
        $quoteMock->method('getCustomerId')->willReturn(0);
        $this->cartRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->quoteId)
            ->willReturn($quoteMock);

        $result = $this->model->execute($this->maskedCartId, $this->quoteId);

        $this->assertSame($quoteMock, $result);
    }

    /**
     * Test execute throws NoSuchEntityException when cart belongs to a customer.
     */
    public function testExecuteThrowsExceptionWhenCustomerCart(): void
    {
        $quoteMock = $this->createQuoteMock();
        $quoteMock->method('getCustomerId')->willReturn(42);
        $this->cartRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->quoteId)
            ->willReturn($quoteMock);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Could not find a cart with ID \'f216207248d65c789b17be8545e0aa73\'');

        $this->model->execute($this->maskedCartId, $this->quoteId);
    }

    /**
     * Test checkIsGuestCart does not throw when customerId is 0.
     */
    public function testCheckIsGuestCartDoesNotThrowWhenGuest(): void
    {
        $this->model->checkIsGuestCart(0, $this->maskedCartId);
        $this->assertTrue(true, 'No exception was thrown for guest cart');
    }

    /**
     * Test checkIsGuestCart throws NoSuchEntityException when customerId is not 0.
     */
    public function testCheckIsGuestCartThrowsExceptionWhenCustomer(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Could not find a cart with ID \'f216207248d65c789b17be8545e0aa73\'');

        $this->model->checkIsGuestCart(42, $this->maskedCartId);
    }
}
