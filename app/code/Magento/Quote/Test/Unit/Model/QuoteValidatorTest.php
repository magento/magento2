<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage as OrderAmountValidationMessage;
use Magento\Quote\Model\QuoteValidator;

/**
 * Class QuoteValidatorTest
 */
class QuoteValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Quote\Model\Quote
     */
    protected $quoteMock;

    /**
     * @var AllowedCountries|\PHPUnit\Framework\MockObject\MockObject
     */
    private $allowedCountryReader;

    /**
     * @var OrderAmountValidationMessage|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderAmountValidationMessage;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->allowedCountryReader = $this->getMockBuilder(AllowedCountries::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderAmountValidationMessage = $this->getMockBuilder(OrderAmountValidationMessage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteValidator = new \Magento\Quote\Model\QuoteValidator(
            $this->allowedCountryReader,
            $this->orderAmountValidationMessage
        );

        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
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
                '__wakeup'
            ]
        );
    }

    public function testCheckQuoteAmountExistingError()
    {
        $this->quoteMock->expects($this->once())
            ->method('getHasError')
            ->willReturn(true);

        $this->quoteMock->expects($this->never())
            ->method('setHasError');

        $this->quoteMock->expects($this->never())
            ->method('addMessage');

        $this->assertSame(
            $this->quoteValidator,
            $this->quoteValidator->validateQuoteAmount($this->quoteMock, QuoteValidator::MAXIMUM_AVAILABLE_NUMBER + 1)
        );
    }
}
