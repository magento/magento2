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
 * Class BillingAddressValidationRule
 * Validates billing address fields in a quote.
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
     * Constructor.
     *
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

        // Validate the billing address
        $validationResult = $billingAddress->validate();
        if ($validationResult !== true) {
            $validationErrors[] = __($this->generalMessage);
        }
        if (is_array($validationResult)) {
            $validationErrors = array_merge($validationErrors, $validationResult);
        }

        // Validate each field
        if (!$this->nameValidator->isValidName($billingAddress->getFirstname())) {
            $validationErrors[] = __('First Name is not valid');
        }
        if (!$this->nameValidator->isValidName($billingAddress->getMiddlename())) {
            $validationErrors[] = __('Middle Name is not valid');
        }
        if (!$this->nameValidator->isValidName($billingAddress->getLastname())) {
            $validationErrors[] = __('Last Name is not valid');
        }
        if (!$this->nameValidator->isValidName($billingAddress->getPrefix())) {
            $validationErrors[] = __('Prefix is not valid');
        }
        if (!$this->nameValidator->isValidName($billingAddress->getSuffix())) {
            $validationErrors[] = __('Suffix is not valid');
        }
        if (!$this->cityValidator->isValidCity($billingAddress->getCity())) {
            $validationErrors[] = __('City is not valid');
        }
        if (!$this->phoneValidator->isValidPhone($billingAddress->getTelephone())) {
            $validationErrors[] = __('Telephone is not valid');
        }
        if (!$this->phoneValidator->isValidPhone($billingAddress->getFax())) {
            $validationErrors[] = __('Fax is not valid');
        }
        if (!$this->streetValidator->isValidStreet($billingAddress->getStreet())) {
            $validationErrors[] = __('Street is not valid');
        }

        // Check if regex validation is enabled
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            'system/security/security_regex_enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($isRegexEnabled) {
            // Validate billing address fields against forbidden patterns
            foreach ($billingAddress->getData() as $key => $value) {
                if (is_string($value) && !$this->forbiddenPatternsValidator->isValid($value)) {
                    $validationErrors[] = __("Field %1 contains invalid characters.", $key);
                }
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
