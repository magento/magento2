<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for PlaceOrder
 */
class PlaceOrderTest extends TestCase
{
    /**
     * @var PlaceOrder
     */
    private  $placeOrder;

    /**
     * @var PaymentMethodManagementInterface|MockObject
     */
    private $paymentManagementMock;

    /**
     * @var CartManagementInterface|MockObject
     */
    private $cartManagementMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentInterface|MockObject
     */
    private $paymentInterfaceMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->paymentManagementMock = $this->createMock(PaymentMethodManagementInterface::class);
        $this->cartManagementMock = $this->createMock(CartManagementInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentInterfaceMock = $this->createMock(PaymentInterface::class);

        $this->placeOrder = new PlaceOrder(
            $this->paymentManagementMock,
            $this->cartManagementMock
        );
    }

    /**
     * Test successful order placement with available payment method
     */
    public function testExecuteWithAvailablePaymentMethod(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;
        $paymentMethodCode = 'checkmo';
        $orderId = 789;

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethodCode);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod1 = $this->createMock(PaymentMethodInterface::class);
        $availableMethod1->method('getCode')->willReturn('paypal');

        $availableMethod2 = $this->createMock(PaymentMethodInterface::class);
        $availableMethod2->method('getCode')->willReturn($paymentMethodCode);

        $availableMethod3 = $this->createMock(PaymentMethodInterface::class);
        $availableMethod3->method('getCode')->willReturn('stripe');

        $availablePaymentMethods = [$availableMethod1, $availableMethod2, $availableMethod3];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $this->cartManagementMock->method('placeOrder')
            ->with($cartId, $this->paymentInterfaceMock)
            ->willReturn($orderId);

        $result = $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);

        $this->assertEquals($orderId, $result);
    }

    /**
     * Test exception when payment method is not available
     */
    public function testExecuteWithUnavailablePaymentMethod(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;
        $paymentMethodCode = 'unavailable_method';

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethodCode);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod1 = $this->createMock(PaymentMethodInterface::class);
        $availableMethod1->method('getCode')->willReturn('paypal');

        $availableMethod2 = $this->createMock(PaymentMethodInterface::class);
        $availableMethod2->method('getCode')->willReturn('stripe');

        $availablePaymentMethods = [$availableMethod1, $availableMethod2];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test exception when no payment method is set on quote
     */
    public function testExecuteWithNoPaymentMethodSet(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn(null);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod = $this->createMock(PaymentMethodInterface::class);
        $availableMethod->method('getCode')->willReturn('checkmo');
        $availablePaymentMethods = [$availableMethod];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test exception when no payment methods are available
     */
    public function testExecuteWithNoAvailablePaymentMethods(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;
        $paymentMethodCode = 'checkmo';

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethodCode);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn([]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test exception when available payment methods is null
     */
    public function testExecuteWithNullAvailablePaymentMethods(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;
        $paymentMethodCode = 'checkmo';

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethodCode);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn(null);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test exception when quote has no payment object
     */
    public function testExecuteWithNoPaymentObject(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn(null);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod = $this->createMock(PaymentMethodInterface::class);
        $availableMethod->method('getCode')->willReturn('checkmo');
        $availablePaymentMethods = [$availableMethod];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test that cart management exceptions are propagated
     */
    public function testExecuteWithCartManagementException(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;
        $paymentMethodCode = 'checkmo';

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethodCode);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod = $this->createMock(PaymentMethodInterface::class);
        $availableMethod->method('getCode')->willReturn($paymentMethodCode);
        $availablePaymentMethods = [$availableMethod];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $expectedException = new NoSuchEntityException(__('Cart does not exist'));
        $this->cartManagementMock->method('placeOrder')
            ->with($cartId, $this->paymentInterfaceMock)
            ->willThrowException($expectedException);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Cart does not exist');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test with empty string payment method code
     */
    public function testExecuteWithEmptyPaymentMethodCode(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn('');

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod = $this->createMock(PaymentMethodInterface::class);
        $availableMethod->method('getCode')->willReturn('checkmo');
        $availablePaymentMethods = [$availableMethod];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }

    /**
     * Test case sensitivity in payment method code comparison
     */
    public function testExecuteWithCaseSensitivePaymentMethodCode(): void
    {
        $cartId = 123;
        $maskedCartId = 'masked123';
        $userId = 456;
        $paymentMethodCode = 'CheckMo'; // Different case
        $orderId = 789;

        $this->quoteMock->method('getId')->willReturn($cartId);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethodCode);

        $this->paymentManagementMock->method('get')
            ->with($cartId)
            ->willReturn($this->paymentInterfaceMock);

        $availableMethod = $this->createMock(PaymentMethodInterface::class);
        $availableMethod->method('getCode')->willReturn('checkmo'); // lowercase
        $availablePaymentMethods = [$availableMethod];

        $this->paymentManagementMock->method('getList')
            ->with($cartId)
            ->willReturn($availablePaymentMethods);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->placeOrder->execute($this->quoteMock, $maskedCartId, $userId);
    }
}
