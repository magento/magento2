<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage as OrderAmountValidationMessage;
use Magento\Quote\Model\QuoteValidator;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteValidatorTest extends TestCase
{
    use MockCreationTrait;
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

        $quoteValidationRule = $this->createStub(QuoteValidationRuleInterface::class);
        $quoteValidationRule->method('validate')->willReturn([]);

        $this->quoteValidator = new QuoteValidator(
            $this->allowedCountryReader,
            $this->orderAmountValidationMessage,
            $quoteValidationRule
        );

        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'getHasError',
                'setHasError',
                'addMessage',
                'getShippingAddress',
                'getBillingAddress',
                'getPayment',
                'isVirtual',
                'validateMinimumAmount',
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
