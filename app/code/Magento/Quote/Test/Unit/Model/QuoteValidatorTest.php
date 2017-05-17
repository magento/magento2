<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\QuoteValidator;

/**
 * Class QuoteValidatorTest
 */
class QuoteValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->quoteValidator = new \Magento\Quote\Model\QuoteValidator();

        $this->quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getShippingAddress',
                'getBillingAddress',
                'getPayment',
                'getHasError',
                'setHasError',
                'addMessage',
                'isVirtual',
                '__wakeup'
            ],
            [],
            '',
            false
        );
    }

    public function testCheckQuoteAmountExistingError()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(true));

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quoteMock, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    public function testCheckQuoteAmountAmountLessThanAvailable()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quoteMock, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER - 1)
        );
    }

    public function testCheckQuoteAmountAmountGreaterThanAvailable()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->will($this->returnValue(false));

        $this->quoteMock->expects($this->once())
            ->method('setHasError')
            ->with(true);

        $this->quoteMock->expects($this->once())
            ->method('addMessage')
            ->with(__('This item price or quantity is not valid for checkout.'));

        $this->assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quoteMock, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the shipping address information.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfShippingAddressIsInvalid()
    {
        $shippingAddressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects($this->any())->method('isVirtual')->willReturn(false);
        $shippingAddressMock->expects($this->any())->method('validate')->willReturn(['Invalid Shipping Address']);

        $this->quoteValidator->validateBeforeSubmit($this->quoteMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify a shipping method.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfShippingRateIsNotSelected()
    {
        $shippingMethod = 'checkmo';
        $shippingAddressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'validate',
                'getShippingMethod',
                'getShippingRateByCode',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects($this->any())->method('isVirtual')->willReturn(false);
        $shippingAddressMock->expects($this->any())->method('validate')->willReturn(true);
        $shippingAddressMock->expects($this->any())->method('getShippingMethod')->willReturn($shippingMethod);
        $shippingAddressMock->expects($this->once())->method('getShippingRateByCode')->with($shippingMethod);

        $this->quoteValidator->validateBeforeSubmit($this->quoteMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the billing address information.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfBillingAddressIsNotValid()
    {
        $billingAddressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->quoteMock->expects($this->any())->method('isVirtual')->willReturn(true);
        $billingAddressMock->expects($this->any())->method('validate')->willReturn(['Invalid Billing Address']);

        $this->quoteValidator->validateBeforeSubmit($this->quoteMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please select a valid payment method.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfPaymentMethodIsNotSelected()
    {
        $paymentMock = $this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false);
        $billingAddressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $billingAddressMock->expects($this->any())->method('validate')->willReturn(true);

        $this->quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->quoteMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);
        $this->quoteMock->expects($this->any())->method('isVirtual')->willReturn(true);

        $this->quoteValidator->validateBeforeSubmit($this->quoteMock);
    }
}
