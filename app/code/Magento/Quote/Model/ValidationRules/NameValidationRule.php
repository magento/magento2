<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Framework\Validator\GlobalNameValidator;

/**
 * Class NameValidationRule
 * Validates the first name, middle name, last name, prefix, and suffix fields in a quote.
 */
class NameValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var GlobalNameValidator
     */
    private $nameValidator;

    /**
     * @var GlobalForbiddenPatterns
     */
    private $forbiddenPatternsValidator;

    /**
     * Constructor.
     *
     * @param ValidationResultFactory $validationResultFactory
     * @param GlobalNameValidator $nameValidator
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        GlobalNameValidator $nameValidator,
        GlobalForbiddenPatterns $forbiddenPatternsValidator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->nameValidator = $nameValidator;
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
    }

    /**
     * Validate the first name, middle name, last name, prefix, and suffix in the quote.
     *
     * @param Quote $quote
     * @return array
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        // Define the fields to validate with their respective validators
        $fieldsToValidate = [
            'First Name' => [$quote->getCustomerFirstname(), 'isValidName', $this->nameValidator],
            'Middle Name' => [$quote->getCustomerMiddlename(), 'isValidName', $this->nameValidator],
            'Last Name' => [$quote->getCustomerLastname(), 'isValidName', $this->nameValidator],
            'Prefix' => [$quote->getCustomerPrefix(), 'isValidName', $this->nameValidator],
            'Suffix' => [$quote->getCustomerSuffix(), 'isValidName', $this->nameValidator],
        ];

        // Validate each field
        foreach ($fieldsToValidate as $fieldName => [$fieldValue, $validationMethod, $validatorInstance]) {
            if (!$validatorInstance->$validationMethod($fieldValue)) {
                $validationErrors[] = __("$fieldName is not valid");
            }
        }

        // Perform regex validation only if no other errors exist
        if (empty($validationErrors)) {
            $this->forbiddenPatternsValidator->validateData($quote->getData(), $validationErrors);
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
