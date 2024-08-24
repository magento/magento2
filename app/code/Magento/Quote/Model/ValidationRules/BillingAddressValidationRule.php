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
use Magento\Framework\Validator\GlobalCityValidator;
use Magento\Framework\Validator\GlobalPhoneValidation;
use Magento\Framework\Validator\GlobalStreetValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class BillingAddressValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

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
     * @param ValidationResultFactory $validationResultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     * @param GlobalNameValidator $nameValidator
     * @param GlobalCityValidator $cityValidator
     * @param GlobalPhoneValidation $phoneValidator
     * @param GlobalStreetValidator $streetValidator
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ScopeConfigInterface $scopeConfig,
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        GlobalNameValidator $nameValidator,
        GlobalCityValidator $cityValidator,
        GlobalPhoneValidation $phoneValidator,
        GlobalStreetValidator $streetValidator,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
        $this->nameValidator = $nameValidator;
        $this->cityValidator = $cityValidator;
        $this->phoneValidator = $phoneValidator;
        $this->streetValidator = $streetValidator;       
        $this->generalMessage = $generalMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setStoreId($quote->getStoreId());
        
        $validationResult = $billingAddress->validate();
        if ($validationResult !== true) {
            $validationErrors = [__($this->generalMessage)];
        }
        if (is_array($validationResult)) {
            $validationErrors = array_merge($validationErrors, $validationResult);
        }

        // Define the fields to validate with their respective validators
        $fieldsToValidate = [
            'First Name' => [$billingAddress->getFirstname(), 'isValidName', GlobalNameValidator::class],
            'Middle Name' => [$billingAddress->getMiddlename(), 'isValidName', GlobalNameValidator::class],
            'Last Name' => [$billingAddress->getLastname(), 'isValidName', GlobalNameValidator::class],
            'Prefix' => [$billingAddress->getPrefix(), 'isValidName', GlobalNameValidator::class],
            'Suffix' => [$billingAddress->getSuffix(), 'isValidName', GlobalNameValidator::class],
            'City' => [$billingAddress->getCity(), 'isValidCity', GlobalCityValidator::class],
            'Telephone' => [$billingAddress->getTelephone(), 'isValidPhone', GlobalPhoneValidation::class],
            'Fax' => [$billingAddress->getFax(), 'isValidPhone', GlobalPhoneValidation::class],
        ];

        // Validate each field
        foreach ($fieldsToValidate as $fieldName => [$fieldValue, $validationMethod, $validatorClass]) {
            if (!$validatorClass::$validationMethod($fieldValue)) {
                $validationErrors[] = __("$fieldName is not valid");
            }
        }

        // Validate each street line if it's an array
        $streetArray = $billingAddress->getStreet();
        if (is_array($streetArray)) {
            foreach ($streetArray as $streetLine) {
                if (!GlobalStreetValidator::isValidStreet($streetLine)) {
                    $validationErrors[] = __('Street is not valid');
                }
            }
        } else {
            if (!GlobalStreetValidator::isValidStreet($streetArray)) {
                $validationErrors[] = __('Street is not valid');
            }
        }

        // Check if regex validation is enabled
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            'system/security/security_regex_enabled',
            ScopeInterface::SCOPE_STORE
        );

        // Perform regex validation only if no other errors exist
        if (empty($validationErrors) && $isRegexEnabled) {
            foreach ($billingAddress->getData() as $key => $value) {
                if (is_string($value) && !$this->forbiddenPatternsValidator->isValid($value)) {
                    $validationErrors[] = __("Field %1 contains invalid characters.", $key);
                }
            }
        }
        
        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
