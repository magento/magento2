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
 * Customer street fields pattern validator.
 */
class StreetValidator extends AbstractValidator
{
    /**
     * Regular expression pattern for validating street addresses.
     * Allowed characters:
     *
     * \p{L}: Unicode letters.
     * \p{M}: Unicode marks (diacritic marks, accents, etc.).
     * ,: Comma.
     * -: Hyphen.
     * .: Period.
     * `'’: Single quotes, both regular and right single quotation marks.
     * &: Ampersand.
     * \s: Whitespace characters (spaces, tabs, newlines, etc.).
     * \d: Digits (0-9).
     * \[\]: Square brackets.
     * \(\): Parentheses.
     *
     * @var string
     */
    public string $patterStreet = "/^[\p{L}\p{M}\,\-\.\'’`&\s\d\[\]\(\)]{1,255}$/u";

    /**
     * XML path for global security pattern validation.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_PATTERN_ENABLED = 'system/security_pattern/security_pattern';
    
    /**
     * XML path for street validation enablement.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_PATTERN_STREET_ENABLED = 'system/security_pattern/security_pattern_street';

    /**
     * Description of allowed characters for street fields.
     *
     * @var string
     */
    public string $allowedCharsDescription = 'A-Z, a-z, 0-9, -, ., \', ’, `, &, space, [, ], (, )';

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
     * Check if both the global security pattern and street validation are enabled in the configuration.
     *
     * @return bool
     */
    public function isValidationEnabled(): bool
    {
        $isGlobalPatternEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $isStreetValidationEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_STREET_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        return $isGlobalPatternEnabled && $isStreetValidationEnabled;
    }

    /**
     * Validate a street address string or an array of street address strings.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (!$this->isValidationEnabled()) {
            return true; // Skip validation if globally disabled
        }

        if (is_array($value)) {
            foreach ($value as $streetValue) {
                if (!$this->validateSingleStreet($streetValue)) {
                    return false;
                }
            }
        } else {
            if (!$this->validateSingleStreet($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate a single street address string.
     *
     * @param mixed $streetValue
     * @return bool
     */
    private function validateSingleStreet($streetValue): bool
    {
        return $this->isValidStreet($streetValue);
    }

    /**
     * Check if the street field is valid.
     *
     * @param mixed $streetValue
     * @return bool
     */
    private function isValidStreet(mixed $streetValue): bool
    {
        if ($streetValue === null || $streetValue === '' || !is_string($streetValue)) {
            return true;
        }

        return preg_match($this->patterStreet, trim($streetValue)) === 1;
    }
}
