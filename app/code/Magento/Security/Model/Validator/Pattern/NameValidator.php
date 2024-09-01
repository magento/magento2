<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Validator\Pattern;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Customer name fields pattern validator.
 */
class NameValidator extends AbstractValidator
{
    private const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'’`&\s\d]){1,255}+/u';
    
    /**
     * Allowed characters for validating names:
     *
     * \p{L}: Unicode letters (e.g., a-z, A-Z, and letters from other languages).
     * \p{M}: Unicode marks (diacritic marks, accents, etc.).
     * ,: Comma, used for separating elements within a name.
     * \-: Hyphen, commonly used in compound names.
     * \_: Underscore, occasionally used in names.
     * \.: Period, often used in initials or abbreviations in names.
     * ': Apostrophe, used in names like "O'Connor".
     * ’: Right single quotation mark, used as an apostrophe in some names.
     * `: Grave accent, used in some names.
     * \&: Ampersand, can appear in business names or titles.
     * \s: Whitespace characters (spaces, tabs, newlines, etc.), allowing multi-part names.
     * \d: Digits (0-9), to allow names that include numbers, such as "John Doe II".
     *
     * The pattern ensures that a name can be between 1 and 255 characters long.
     */
    public string $patternName = '/^(?:[\p{L}\p{M},\-_.\'’`&\s\d]{1,255})$/u';
    
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
    public const XML_PATH_SECURITY_PATTERN_NAME_ENABLED = 'system/security_pattern/security_pattern_name';
    
    /**
     * Description of allowed characters for name fields.
     *
     * @var string
     */
    public string $allowedCharsDescription = 'A-Z, a-z, 0-9, -, _, ., \', ’, `, &, space';

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
     * Check if both the global security pattern and name validation are enabled in the configuration.
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

        // Check if the specific name validation is enabled
        $isNameValidationEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_NAME_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        // Return true only if both are enabled
        return $isGlobalPatternEnabled && $isNameValidationEnabled;
    }

    /**
     * Validate the name value against the pattern.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if ($value === null || $value === '' || !is_string($value)) {
            return true;
        }

        $pattern = $this->isValidationEnabled() ? $this->patternName : self::PATTERN_NAME;

        return preg_match($pattern, trim($value)) === 1;
    }
}
