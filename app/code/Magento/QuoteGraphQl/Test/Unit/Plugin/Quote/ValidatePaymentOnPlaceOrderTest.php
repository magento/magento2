<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Plugin\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\QuoteGraphQl\Plugin\Quote\ValidatePaymentOnPlaceOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatePaymentOnPlaceOrderTest extends TestCase
{
    /** @var CartRepositoryInterface&MockObject */
    private CartRepositoryInterface $cartRepository;

    /** @var PaymentHelper&MockObject */
    private PaymentHelper $paymentHelper;

    /**
     * @var ValidatePaymentOnPlaceOrder
     */
    private ValidatePaymentOnPlaceOrder $plugin;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->paymentHelper = $this->createMock(PaymentHelper::class);

        $this->plugin = new ValidatePaymentOnPlaceOrder(
            $this->cartRepository,
            $this->paymentHelper
        );
    }

    public function testReturnsArgsWhenNoPaymentCode(): void
    {
        $subject = $this->createMock(QuoteManagement::class);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPayment'])
            ->getMock();

        $payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethod'])
            ->getMock();

        $this->cartRepository
            ->expects($this->once())
            ->method('getActive')
            ->with($this->identicalTo(123))
            ->willReturn($quote);

        $quote
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $payment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn(null);

        $this->paymentHelper
            ->expects($this->never())
            ->method('getMethodInstance');

        $result = $this->plugin->beforePlaceOrder($subject, '123', null);

        $this->assertSame(['123', null], $result);
    }

    public function testUsesProvidedPaymentMethodCodeAndIsAvailable(): void
    {
        $subject = $this->createMock(QuoteManagement::class);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPayment'])
            ->getMock();

        $payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethod'])
            ->getMock();

        $paymentMethodParam = $this->createMock(PaymentInterface::class);
        $methodInstance = $this->createMock(MethodInterface::class);

        $this->cartRepository
            ->expects($this->once())
            ->method('getActive')
            ->with($this->identicalTo(10))
            ->willReturn($quote);

        $quote
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $paymentMethodParam
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('checkmo');

        $this->paymentHelper
            ->expects($this->once())
            ->method('getMethodInstance')
            ->with('checkmo')
            ->willReturn($methodInstance);

        $methodInstance
            ->expects($this->once())
            ->method('setInfoInstance')
            ->with($payment);

        $methodInstance
            ->expects($this->once())
            ->method('isAvailable')
            ->with($quote)
            ->willReturn(true);

        $result = $this->plugin->beforePlaceOrder($subject, '10', $paymentMethodParam);

        $this->assertSame(['10', $paymentMethodParam], $result);
    }

    public function testFallsBackToQuotePaymentCodeWhenNoParamProvided(): void
    {
        $subject = $this->createMock(QuoteManagement::class);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPayment'])
            ->getMock();

        $payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethod'])
            ->getMock();

        $methodInstance = $this->createMock(MethodInterface::class);

        $this->cartRepository
            ->expects($this->once())
            ->method('getActive')
            ->with($this->identicalTo(77))
            ->willReturn($quote);

        $quote
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $payment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('banktransfer');

        $this->paymentHelper
            ->expects($this->once())
            ->method('getMethodInstance')
            ->with('banktransfer')
            ->willReturn($methodInstance);

        $methodInstance
            ->expects($this->once())
            ->method('setInfoInstance')
            ->with($payment);

        $methodInstance
            ->expects($this->once())
            ->method('isAvailable')
            ->with($quote)
            ->willReturn(true);

        $result = $this->plugin->beforePlaceOrder($subject, '77', null);

        $this->assertSame(['77', null], $result);
    }

    public function testThrowsWhenMethodNotAvailable(): void
    {
        $subject = $this->createMock(QuoteManagement::class);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPayment'])
            ->getMock();

        $payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethod'])
            ->getMock();

        $paymentMethodParam = $this->createMock(PaymentInterface::class);
        $methodInstance = $this->createMock(MethodInterface::class);

        $this->cartRepository
            ->expects($this->once())
            ->method('getActive')
            ->with($this->identicalTo(5))
            ->willReturn($quote);

        $quote
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $paymentMethodParam
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('cc');

        $this->paymentHelper
            ->expects($this->once())
            ->method('getMethodInstance')
            ->with('cc')
            ->willReturn($methodInstance);

        $methodInstance
            ->expects($this->once())
            ->method('setInfoInstance')
            ->with($payment);

        $methodInstance
            ->expects($this->once())
            ->method('isAvailable')
            ->with($quote)
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The requested Payment Method is not available.');

        $this->plugin->beforePlaceOrder($subject, '5', $paymentMethodParam);
    }
}
