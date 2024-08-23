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
     * @param ValidationResultFactory $validationResultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ScopeConfigInterface $scopeConfig,
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
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
}
