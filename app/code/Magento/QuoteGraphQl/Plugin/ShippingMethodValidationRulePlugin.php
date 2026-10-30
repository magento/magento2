<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Quote\Model\ValidationRules\ShippingMethodValidationRule;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

class ShippingMethodValidationRulePlugin
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * After plugin for validate method to ensure shipping method validity.
     *
     * @param ShippingMethodValidationRule $subject
     * @param ValidationResult[] $result
     * @param Quote $quote
     * @return ValidationResult[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterValidate(
        ShippingMethodValidationRule $subject,
        array $result,
        Quote $quote
    ): array {
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress || $quote->isVirtual()) {
            return $result;
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        $shippingRate = $shippingMethod ? $shippingAddress->getShippingRateByCode($shippingMethod) : null;
        $validationResult = $shippingMethod && $shippingRate && $shippingAddress->requestShippingRates();

        if ($validationResult) {
            return $result;
        }

        $existing = $result[0] ?? null;
        if ($existing instanceof ValidationResult && $existing->isValid()) {
            $result[0] = $this->validationResultFactory->create([
                'errors' => [__('The shipping method is missing. Select the shipping method and try again')]
            ]);
        }

        return $result;
    }
}
