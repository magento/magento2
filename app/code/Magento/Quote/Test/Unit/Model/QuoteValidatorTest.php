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
<<<<<<< HEAD
        $this->quote->method('getHasError')
            ->willReturn(true);

        $this->quote->expects(self::never())
            ->method('setHasError');

        $this->quote->expects(self::never())
            ->method('addMessage');

        self::assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quote, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER + 1)
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
    }
}
