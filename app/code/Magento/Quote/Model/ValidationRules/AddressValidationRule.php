<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Framework\Validator\GlobalNameValidator;
use Magento\Framework\Validator\GlobalCityValidator;
use Magento\Framework\Validator\GlobalPhoneValidation;
use Magento\Framework\Validator\GlobalStreetValidator;

class AddressValidationRule
{
    /**
     * @var GlobalForbiddenPatterns
     */
    private $forbiddenPatternsValidator;

    /**
     * @var GlobalNameValidator
     */
    private $nameValidator;

    /**
     * @var GlobalCityValidator
     */
    private $cityValidator;

    /**
     * @var GlobalPhoneValidation
     */
    private $phoneValidator;

    /**
     * @var GlobalStreetValidator
     */
    private $streetValidator;

    /**
     * Constructor
     *
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     * @param GlobalNameValidator $nameValidator
     * @param GlobalCityValidator $cityValidator
     * @param GlobalPhoneValidation $phoneValidator
     * @param GlobalStreetValidator $streetValidator
     */
    public function __construct(
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        GlobalNameValidator $nameValidator,
        GlobalCityValidator $cityValidator,
        GlobalPhoneValidation $phoneValidator,
        GlobalStreetValidator $streetValidator
    ) {
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
        $this->nameValidator = $nameValidator;
        $this->cityValidator = $cityValidator;
        $this->phoneValidator = $phoneValidator;
        $this->streetValidator = $streetValidator;
    }

    /**
     * Validates the address fields and applies forbidden pattern checks
     *
     * @param mixed $address           The address object to validate.
     * @param array &$validationErrors An array to store validation errors.
     * @return void
     */
    public function validateAddress($address, array &$validationErrors): void
    {
        // Define the fields to validate with their respective validators
        $fieldsToValidate = [
            'First Name' => [$address->getFirstname(), 'isValidName', $this->nameValidator],
            'Middle Name' => [$address->getMiddlename(), 'isValidName', $this->nameValidator],
            'Last Name' => [$address->getLastname(), 'isValidName', $this->nameValidator],
            'Prefix' => [$address->getPrefix(), 'isValidName', $this->nameValidator],
            'Suffix' => [$address->getSuffix(), 'isValidName', $this->nameValidator],
            'City' => [$address->getCity(), 'isValidCity', $this->cityValidator],
            'Telephone' => [$address->getTelephone(), 'isValidPhone', $this->phoneValidator],
            'Fax' => [$address->getFax(), 'isValidPhone', $this->phoneValidator],
            'Street' => [$address->getStreet(), 'isValidStreet', $this->streetValidator],
        ];

        // Validate each field
        foreach ($fieldsToValidate as $fieldName => [$fieldValue, $validationMethod, $validatorInstance]) {
            if (is_array($fieldValue)) {
                foreach ($fieldValue as $value) {
                    if (!$validatorInstance->$validationMethod($value)) {
                        error_log("Invalid value: " . $fieldValue);
                        $validationErrors[] = __("$fieldName is not valid");
                    }
                }
            } else {
                if (!$validatorInstance->$validationMethod($fieldValue)) {
                    error_log("Invalid value: " . $fieldValue);
                    $validationErrors[] = __("$fieldName is not valid");
                }
            }
        }

        if (empty($validationErrors)) {
            $this->forbiddenPatternsValidator->validateData($address->getData(), $validationErrors);
        }
    }
}
