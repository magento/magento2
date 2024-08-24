<?php
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
class ShippingAddressValidationRule implements QuoteValidationRuleInterface
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

        if (!$quote->isVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setStoreId($quote->getStoreId());

            // Validate the shipping address
            $validationResult = $shippingAddress->validate();

            if ($validationResult !== true) {
                $validationErrors = [__($this->generalMessage)];
            }
            if (is_array($validationResult)) {
                $validationErrors = array_merge($validationErrors, $validationResult);
            }
            
            // Define the fields to validate with their respective validators
            $fieldsToValidate = [
                'First Name' => [$shippingAddress->getFirstname(), 'isValidName', $this->nameValidator],
                'Middle Name' => [$shippingAddress->getMiddlename(), 'isValidName', $this->nameValidator],
                'Last Name' => [$shippingAddress->getLastname(), 'isValidName', $this->nameValidator],
                'Prefix' => [$shippingAddress->getPrefix(), 'isValidName', $this->nameValidator],
                'Suffix' => [$shippingAddress->getSuffix(), 'isValidName', $this->nameValidator],
                'City' => [$shippingAddress->getCity(), 'isValidCity', $this->cityValidator],
                'Telephone' => [$shippingAddress->getTelephone(), 'isValidPhone', $this->phoneValidator],
                'Fax' => [$shippingAddress->getFax(), 'isValidPhone', $this->phoneValidator],
                'Street' => [$shippingAddress->getStreet(), 'isValidStreet', $this->streetValidator],
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
                'system/security/security_regex_enabled',
                ScopeInterface::SCOPE_STORE
            );

            // Perform regex validation only if no other errors exist
            if (empty($validationErrors) && $isRegexEnabled) {
                foreach ($shippingAddress->getData() as $key => $value) {
                    if (is_string($value) && !$this->forbiddenPatternsValidator->isValid($value)) {
                        $validationErrors[] = __("Field %1 contains invalid characters.", $key);
                    }
                }
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
