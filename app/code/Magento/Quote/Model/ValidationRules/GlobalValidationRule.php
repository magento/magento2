<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Quote\Model\Quote;

/**
 * Class GlobalValidationRule
 * Validates all fields in a quote.
 */
class GlobalValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * Constructor.
     *
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * Extracts data from the quote object for validation.
     *
     * @param Quote $quote
     * @return array
     */
    private function extractQuoteData(Quote $quote): array
    {
        $data = $quote->getData();

        if ($billingAddress = $quote->getBillingAddress()) {
            $data = array_merge($data, $billingAddress->getData());
        }

        if ($shippingAddress = $quote->getShippingAddress()) {
            $data = array_merge($data, $shippingAddress->getData());
        }

        return $data;
    }

    /**
     * Validates the global input fields in the quote.
     *
     * @param Quote $quote
     * @return ValidationResult
     */
    public function validate(Quote $quote): ValidationResult
    {
        $validationResult = $this->validateGlobalInput($quote);
        return $validationResult->getErrors();
    }

    /**
     * Validates the global input fields extracted from the quote.
     *
     * @param Quote $quote
     * @return ValidationResult
     */
    public function validateGlobalInput(Quote $quote): ValidationResult
    {
        $validationErrors = [];
        $inputArray = $this->extractQuoteData($quote);

        foreach ($inputArray as $key => $value) {
            if (is_string($value) && !$this->isValid($value)) {
                $validationErrors[] = __("Field $key contains invalid characters.");
            }
        }

        return $this->validationResultFactory->create(['errors' => $validationErrors]);
    }

    /**
     * Checks if the given field value is valid according to the forbidden patterns.
     *
     * @param string $fieldValue
     * @return bool
     */
    private function isValid(string $fieldValue): bool
    {
        foreach (GlobalForbiddenPatterns::getPatterns() as $pattern) {
            if (preg_match($pattern, $fieldValue)) {
                return false;
            }
        }
        return true;
    }
}
