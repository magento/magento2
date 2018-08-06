<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage as OrderAmountValidationMessage;
use Magento\Quote\Model\QuoteValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class QuoteValidatorTest
 */
class QuoteValidatorTest extends \PHPUnit\Framework\TestCase
{
    private static $storeId = 2;

    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    private $quoteValidator;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var AllowedCountries|MockObject
     */
    private $allowedCountryReader;

    /**
     * @var OrderAmountValidationMessage|MockObject
     */
    private $orderAmountValidationMessage;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->allowedCountryReader = $this->getMockBuilder(AllowedCountries::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderAmountValidationMessage = $this->getMockBuilder(OrderAmountValidationMessage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteValidator = new QuoteValidator(
            $this->allowedCountryReader,
            $this->orderAmountValidationMessage
        );

        $this->quote = $this->createPartialMock(
            Quote::class,
            [
                'getShippingAddress',
                'getBillingAddress',
                'getPayment',
                'getHasError',
                'setHasError',
                'addMessage',
                'isVirtual',
                'validateMinimumAmount',
                'getIsMultiShipping',
                'getStoreId'
            ]
        );
        $this->quote->method('getStoreId')
            ->willReturn(self::$storeId);
    }

    public function testCheckQuoteAmountExistingError()
    {
        $this->quote->method('getHasError')
            ->willReturn(true);

        $this->quote->expects(self::never())
            ->method('setHasError');

        $this->quote->expects(self::never())
            ->method('addMessage');

        self::assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quote, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    public function testCheckQuoteAmountAmountLessThanAvailable()
    {
        $this->quote->method('getHasError')
            ->willReturn(false);

        $this->quote->expects(self::never())
            ->method('setHasError');

        $this->quote->expects(self::never())
            ->method('addMessage');

        self::assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quote, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER - 1)
        );
    }

    public function testCheckQuoteAmountAmountGreaterThanAvailable()
    {
        $this->quote ->method('getHasError')
            ->willReturn(false);

        $this->quote->method('setHasError')
            ->with(true);

        $this->quote->method('addMessage')
            ->with(__('This item price or quantity is not valid for checkout.'));

        self::assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quote, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the shipping address information.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfShippingAddressIsInvalid()
    {
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'validate'])
            ->getMock();
        $this->quote->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $shippingAddress->expects(self::atLeastOnce())
            ->method('setStoreId')
            ->with(self::$storeId);
        $shippingAddress->method('validate')
            ->willReturn(['Invalid Shipping Address']);

        $this->quoteValidator->validateBeforeSubmit($this->quote);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify a shipping method.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfShippingRateIsNotSelected()
    {
        $shippingMethod = 'checkmo';
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'validate', 'getCountryId', 'getShippingMethod', 'getShippingRateByCode'])
            ->getMock();

        $this->allowedCountryReader->method('getAllowedCountries')
            ->willReturn(['US' => 'US']);

        $this->quote ->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $shippingAddress->expects(self::atLeastOnce())
            ->method('setStoreId')
            ->with(self::$storeId);
        $shippingAddress->method('validate')
            ->willReturn(true);
        $shippingAddress->method('getCountryId')
            ->willReturn('US');
        $shippingAddress->method('getShippingMethod')
            ->willReturn($shippingMethod);
        $shippingAddress->method('getShippingRateByCode')
            ->with($shippingMethod);

        $this->quoteValidator->validateBeforeSubmit($this->quote);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the billing address information.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfBillingAddressIsNotValid()
    {
        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'validate'])
            ->getMock();
        $this->quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->quote->method('isVirtual')
            ->willReturn(true);
        $billingAddress->expects(self::atLeastOnce())
            ->method('setStoreId')
            ->with(self::$storeId);
        $billingAddress->method('validate')
            ->willReturn(['Invalid Billing Address']);

        $this->quoteValidator->validateBeforeSubmit($this->quote);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please select a valid payment method.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfPaymentMethodIsNotSelected()
    {
        $payment = $this->createMock(Payment::class);
        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'validate'])
            ->getMock();
        $billingAddress->expects(self::atLeastOnce())
            ->method('setStoreId')
            ->with(self::$storeId);
        $billingAddress->method('validate')
            ->willReturn(true);

        $this->quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->quote->method('getPayment')
            ->willReturn($payment);
        $this->quote->method('isVirtual')
            ->willReturn(true);

        $this->quoteValidator->validateBeforeSubmit($this->quote);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Minimum Order Amount Exceeded.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfMinimumOrderAmount()
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')
            ->willReturn('checkmo');

        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate', 'setStoreId'])
            ->getMock();
        $billingAddress->expects(self::atLeastOnce())
            ->method('setStoreId')
            ->with(self::$storeId);
        $billingAddress->method('validate')
            ->willReturn(true);

        $this->quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->quote->method('getPayment')
            ->willReturn($payment);
        $this->quote->method('isVirtual')
            ->willReturn(true);

        $this->quote->method('getIsMultiShipping')
            ->willReturn(false);
        $this->quote->method('validateMinimumAmount')
            ->willReturn(false);

        $this->orderAmountValidationMessage->method('getMessage')
            ->willReturn(__("Minimum Order Amount Exceeded."));

        $this->quoteValidator->validateBeforeSubmit($this->quote);
    }

    /**
     * Test case when country id not present in allowed countries list.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some addresses cannot be used due to country-specific configurations.
     */
    public function testValidateBeforeSubmitThrowsExceptionIfCountrySpecificConfigurations()
    {
        $this->allowedCountryReader->method('getAllowedCountries')
            ->with('store', self::$storeId)
            ->willReturn(['EE' => 'EE']);

        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'validate', 'getCountryId'])
            ->getMock();
        $address->expects(self::atLeastOnce())
            ->method('setStoreId')
            ->with(self::$storeId);
        $address->method('validate')
            ->willReturn(true);
        $address->method('getCountryId')
            ->willReturn('EU');

        $payment = $this->getMockBuilder(Payment::class)
            ->setMethods(['getMethod'])
            ->disableOriginalConstructor()
            ->getMock();
        $payment->method('getMethod')
            ->willReturn(true);

        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $billingAddress->method('validate')
            ->willReturn(true);

        $this->quote->method('getShippingAddress')
            ->willReturn($address);
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $this->quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->quote->method('getPayment')
            ->willReturn($payment);

        $this->quoteValidator->validateBeforeSubmit($this->quote);
    }
}
