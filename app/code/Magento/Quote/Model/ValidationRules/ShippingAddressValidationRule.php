<?php
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validator\GlobalCityValidator;
use Magento\Framework\Validator\GlobalNameValidator;
use Magento\Framework\Validator\GlobalPhoneValidation;
use Magento\Framework\Validator\GlobalStreetValidator;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Shipping Address Validation Rule
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
     * @var GlobalCityValidator
     */
    private $cityValidator;

    /**
     * @var GlobalNameValidator
     */
    private $nameValidator;

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
     * @param GlobalCityValidator $cityValidator
     * @param GlobalNameValidator $nameValidator
     * @param GlobalPhoneValidation $phoneValidator
     * @param GlobalStreetValidator $streetValidator
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ScopeConfigInterface $scopeConfig,
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        GlobalCityValidator $cityValidator,
        GlobalNameValidator $nameValidator,
        GlobalPhoneValidation $phoneValidator,
        GlobalStreetValidator $streetValidator,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
        $this->cityValidator = $cityValidator;
        $this->nameValidator = $nameValidator;
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

            // Validate specific fields against the corresponding validators
            $this->validateField($shippingAddress->getCity(), 'City', $this->cityValidator, $validationErrors);
            $this->validateField($shippingAddress->getFirstname(), 'First Name', $this->nameValidator, $validationErrors);
            $this->validateField($shippingAddress->getMiddlename(), 'Middle Name', $this->nameValidator, $validationErrors);
            $this->validateField($shippingAddress->getLastname(), 'Last Name', $this->nameValidator, $validationErrors);
            $this->validateField($shippingAddress->getPrefix(), 'Prefix', $this->nameValidator, $validationErrors);
            $this->validateField($shippingAddress->getSuffix(), 'Suffix', $this->nameValidator, $validationErrors);
            $this->validateField($shippingAddress->getTelephone(), 'Telephone', $this->phoneValidator, $validationErrors);
            $this->validateField($shippingAddress->getFax(), 'Fax', $this->phoneValidator, $validationErrors);
            $this->validateField($shippingAddress->getStreet(), 'Street', $this->streetValidator, $validationErrors);

            // Check if regex validation is enabled
            $isRegexEnabled = $this->scopeConfig->isSetFlag(
                'system/security/security_regex_enabled',
                ScopeInterface::SCOPE_STORE
            );

            if ($isRegexEnabled) {
                // Validate shipping address fields against forbidden patterns
                foreach ($shippingAddress->getData() as $key => $value) {
                    if (is_string($value) && !$this->forbiddenPatternsValidator->isValid($value)) {
                        $validationErrors[] = __("Field %1 contains invalid characters.", $key);
                    }
                }
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }

    /**
     * Validate a specific field
     *
     * @param string|null $fieldValue
     * @param string $fieldName
     * @param object $validator
     * @param array $validationErrors
     */
    private function validateField(?string $fieldValue, string $fieldName, $validator, &$validationErrors)
    {
        if ($fieldValue !== null && !$validator->isValid($fieldValue)) {
            $validationErrors[] = __("Invalid %1.", $fieldName);
        }
    }
}
