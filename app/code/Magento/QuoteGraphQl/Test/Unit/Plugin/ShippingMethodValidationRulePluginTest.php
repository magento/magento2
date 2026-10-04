<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Plugin;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ValidationRules\ShippingMethodValidationRule;
use Magento\QuoteGraphQl\Plugin\ShippingMethodValidationRulePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingMethodValidationRulePluginTest extends TestCase
{
    /** @var ValidationResultFactory|MockObject */
    private $validationResultFactory;

    /** @var ShippingMethodValidationRulePlugin */
    private $plugin;

    protected function setUp(): void
    {
        $this->validationResultFactory = $this->createMock(ValidationResultFactory::class);
        $this->plugin = new ShippingMethodValidationRulePlugin($this->validationResultFactory);
    }

    public function testReturnsOriginalResultWhenNoShippingAddress(): void
    {
        $subject = $this->createMock(ShippingMethodValidationRule::class);
        $quote = $this->createMock(Quote::class);
        $quote->method('getShippingAddress')->willReturn(null);
        $quote->method('isVirtual')->willReturn(false);

        $existingValidation = $this->createMock(ValidationResult::class);
        $existingValidation->method('isValid')->willReturn(true);
        $result = [$existingValidation];

        $this->validationResultFactory->expects($this->never())->method('create');

        $actual = $this->plugin->afterValidate($subject, $result, $quote);

        $this->assertSame($result[0], $actual[0]);
    }

    public function testReturnsOriginalResultWhenQuoteIsVirtual(): void
    {
        $subject = $this->createMock(ShippingMethodValidationRule::class);
        $quote = $this->createMock(Quote::class);
        $address = $this->createMock(Address::class);

        $quote->method('getShippingAddress')->willReturn($address);
        $quote->method('isVirtual')->willReturn(true);

        $existingValidation = $this->createMock(ValidationResult::class);
        $existingValidation->method('isValid')->willReturn(true);
        $result = [$existingValidation];

        $this->validationResultFactory->expects($this->never())->method('create');

        $actual = $this->plugin->afterValidate($subject, $result, $quote);

        $this->assertSame($result[0], $actual[0]);
    }

    public function testReturnsOriginalResultWhenShippingIsValid(): void
    {
        $subject = $this->createMock(ShippingMethodValidationRule::class);
        $quote = $this->createMock(Quote::class);
        $address = $this->createMock(Address::class);

        $quote->method('getShippingAddress')->willReturn($address);
        $quote->method('isVirtual')->willReturn(false);

        $methodCode = 'flatrate_flatrate';
        $address->method('getShippingMethod')->willReturn($methodCode);
        $address->method('getShippingRateByCode')->with($methodCode)->willReturn(new \stdClass());
        $address->method('requestShippingRates')->willReturn(true);

        $existingValidation = $this->createMock(ValidationResult::class);
        $existingValidation->method('isValid')->willReturn(true);
        $result = [$existingValidation];

        $this->validationResultFactory->expects($this->never())->method('create');

        $actual = $this->plugin->afterValidate($subject, $result, $quote);

        $this->assertSame($result[0], $actual[0]);
    }

    public function testReplacesResultWhenInvalidShippingAndExistingIsValid(): void
    {
        $subject = $this->createMock(ShippingMethodValidationRule::class);
        $quote = $this->createMock(Quote::class);
        $address = $this->createMock(Address::class);

        $quote->method('getShippingAddress')->willReturn($address);
        $quote->method('isVirtual')->willReturn(false);

        // Invalid shipping: no method set (could also be rate null or requestShippingRates false)
        $address->method('getShippingMethod')->willReturn(null);

        $existingValid = $this->createMock(ValidationResult::class);
        $existingValid->method('isValid')->willReturn(true);
        $result = [$existingValid];

        $replacement = $this->createMock(ValidationResult::class);

        $this->validationResultFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return is_array($params)
                    && array_key_exists('errors', $params)
                    && is_array($params['errors'])
                    && count($params['errors']) === 1;
            }))
            ->willReturn($replacement);

        $actual = $this->plugin->afterValidate($subject, $result, $quote);

        $this->assertSame($replacement, $actual[0]);
    }

    public function testDoesNotReplaceWhenInvalidShippingAndExistingIsInvalid(): void
    {
        $subject = $this->createMock(ShippingMethodValidationRule::class);
        $quote = $this->createMock(Quote::class);
        $address = $this->createMock(Address::class);

        $quote->method('getShippingAddress')->willReturn($address);
        $quote->method('isVirtual')->willReturn(false);

        $address->method('getShippingMethod')->willReturn(null);

        $existingInvalid = $this->createMock(ValidationResult::class);
        $existingInvalid->method('isValid')->willReturn(false);
        $result = [$existingInvalid];

        $this->validationResultFactory->expects($this->never())->method('create');

        $actual = $this->plugin->afterValidate($subject, $result, $quote);

        $this->assertSame($existingInvalid, $actual[0]);
    }
}
