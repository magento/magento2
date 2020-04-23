<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage as OrderAmountValidationMessage;
use Magento\Quote\Model\QuoteValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteValidatorTest extends TestCase
{
    /**
     * @var QuoteValidator
     */
    protected $quoteValidator;

    /**
     * @var MockObject|Quote
     */
    protected $quoteMock;

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
    protected function setUp(): void
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

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getHasError', 'getIsMultiShipping'])
            ->onlyMethods(
                [
                    'getShippingAddress',
                    'getBillingAddress',
                    'getPayment',
                    'setHasError',
                    'addMessage',
                    'isVirtual',
                    'validateMinimumAmount',
                    '__wakeup'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
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
