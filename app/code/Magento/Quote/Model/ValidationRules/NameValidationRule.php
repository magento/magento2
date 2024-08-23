<?php
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor.
     *
     * @param ValidationResultFactory $validationResultFactory
     * @param GlobalNameValidator $nameValidator
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        GlobalNameValidator $nameValidator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->nameValidator = $nameValidator;
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

        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            'system/security/security_regex_enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($isRegexEnabled) {
            $firstName = $quote->getCustomerFirstname();
            $middleName = $quote->getCustomerMiddlename();
            $lastName = $quote->getCustomerLastname();
            $customerPrefix = $quote->getCustomerPrefix();
            $customerSuffix = $quote->getCustomerSuffix();

            // Validate each name-related field
            if (!GlobalNameValidator::isValidName($firstName)) {
                $validationErrors[] = __('First Name is not valid');
            }
            if (!GlobalNameValidator::isValidName($middleName)) {
                $validationErrors[] = __('Middle Name is not valid');
            }
            if (!GlobalNameValidator::isValidName($lastName)) {
                $validationErrors[] = __('Last Name is not valid');
            }
            if (!GlobalNameValidator::isValidName($customerPrefix)) {
                $validationErrors[] = __('Prefix is not valid');
            }
            if (!GlobalNameValidator::isValidName($customerSuffix)) {
                $validationErrors[] = __('Suffix is not valid');
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
