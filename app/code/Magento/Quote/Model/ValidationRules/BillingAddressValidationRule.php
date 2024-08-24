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
            'First Name' => [$billingAddress->getFirstname(), 'isValidName', $this->nameValidator],
            'Middle Name' => [$billingAddress->getMiddlename(), 'isValidName', $this->nameValidator],
            'Last Name' => [$billingAddress->getLastname(), 'isValidName', $this->nameValidator],
            'Prefix' => [$billingAddress->getPrefix(), 'isValidName', $this->nameValidator],
            'Suffix' => [$billingAddress->getSuffix(), 'isValidName', $this->nameValidator],
            'City' => [$billingAddress->getCity(), 'isValidCity', $this->cityValidator],
            'Telephone' => [$billingAddress->getTelephone(), 'isValidPhone', $this->phoneValidator],
            'Fax' => [$billingAddress->getFax(), 'isValidPhone', $this->phoneValidator],
            'Street' => [$billingAddress->getStreet(), 'isValidStreet', $this->streetValidator],
        ];

        // Validate each field
        foreach ($fieldsToValidate as $fieldName => [$fieldValue, $validationMethod, $validatorInstance]) {
            if (is_array($fieldValue)) {
                foreach ($fieldValue as $value) {
                    if (!$validatorInstance->$validationMethod($value)) {
                        $validationErrors[] = __("$fieldName is not valid");
                    }
                }
            } else {
                if (!$validatorInstance->$validationMethod($fieldValue)) {
                    $validationErrors[] = __("$fieldName is not valid");
                }
            }
        }

        // Check if regex validation is enabled
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            GlobalForbiddenPatterns::XML_PATH_SECURITY_REGEX_ENABLED,
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
