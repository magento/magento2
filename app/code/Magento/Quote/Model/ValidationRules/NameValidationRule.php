<?php
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Framework\Validator\GlobalNameValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor.
     *
     * @param ValidationResultFactory $validationResultFactory
     * @param GlobalNameValidator $nameValidator
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        GlobalNameValidator $nameValidator,
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->nameValidator = $nameValidator;
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
        $this->scopeConfig = $scopeConfig;
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
            'First Name' => [$quote->getCustomerFirstname(), 'isValidName', GlobalNameValidator::class],
            'Middle Name' => [$quote->getCustomerMiddlename(), 'isValidName', GlobalNameValidator::class],
            'Last Name' => [$quote->getCustomerLastname(), 'isValidName', GlobalNameValidator::class],
            'Prefix' => [$quote->getCustomerPrefix(), 'isValidName', GlobalNameValidator::class],
            'Suffix' => [$quote->getCustomerSuffix(), 'isValidName', GlobalNameValidator::class],
        ];

        // Validate each field
        foreach ($fieldsToValidate as $fieldName => [$fieldValue, $validationMethod, $validatorClass]) {
            if (!$validatorClass::$validationMethod($fieldValue)) {
                $validationErrors[] = __("$fieldName is not valid");
            }
        }

        // Check if regex validation is enabled
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            'system/security/security_regex_enabled',
            ScopeInterface::SCOPE_STORE
        );

        // Perform regex validation only if no other errors exist
        if (empty($validationErrors) && $isRegexEnabled) {
            foreach ($quote->getData() as $key => $value) {
                if (is_string($value) && !$this->forbiddenPatternsValidator->isValid($value)) {
                    $validationErrors[] = __("Field %1 contains invalid characters.", $key);
                }
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
