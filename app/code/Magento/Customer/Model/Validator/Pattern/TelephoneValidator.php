<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator\Pattern;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Customer phone fields pattern validator.
 */
class TelephoneValidator extends AbstractValidator
{
    /**
     * Allowed characters for validating phone numbers:
     *
     * \d: Digits (0-9), representing the numbers in a phone number.
     * \s: Whitespace characters (spaces, tabs, newlines, etc.), allowing separation within the number.
     * \+: Plus sign, often used to indicate the country code (e.g., +1 for the USA).
     * \-: Hyphen, commonly used to separate different parts of a phone number (e.g., 555-1234).
     * \(: Opening parenthesis, often used around area codes (e.g., (555) 123-4567).
     * \): Closing parenthesis, used with the opening parenthesis around area codes.
     * \/: Forward slash, sometimes used in extensions or other parts of the number.
     *
     * The pattern ensures that a phone number can be between 1 and 40 characters long.
     *
     * @var string
     */
    public string $patternTelephone = '/^[\d\s\+\-\()\/]{1,40}$/u';
    
    /**
     * XML path for regex validation.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_PATTERN_ENABLED = 'system/security_pattern/security_pattern';
    
    /**
     * XML path for regex validation.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_PATTERN_TELEPHONE_ENABLED = 'system/security_pattern/security_pattern_telephone';
    
    /**
     * Description of allowed characters for telephone fields.
     *
     * @var string
     */
    public string $allowedCharsDescription = '0-9, +, -, (, ), /, space';
    
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if both the global security pattern and telephone validation are enabled in the configuration.
     *
     * @return bool
     */
    public function isValidationEnabled(): bool
    {
        // Check if the global security pattern validation is enabled
        $isGlobalPatternEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        // Check if the specific telephone validation is enabled
        $isTelephoneValidationEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_TELEPHONE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        // Return true only if both are enabled
        return $isGlobalPatternEnabled && $isTelephoneValidationEnabled;
    }

    /**
     * Validate the telephone value against the pattern.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match($this->patternTelephone, trim($value)) === 1;
    }
}
