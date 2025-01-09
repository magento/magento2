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
 * Customer city fields pattern validator.
 */
class CityValidator extends AbstractValidator
{
    /**
     * Allowed characters:
     *
     * \p{L}: Unicode letters.
     * \p{M}: Unicode marks (diacritic marks, accents, etc.).
     * ': Apostrophe mark.
     * \s: Whitespace characters (spaces, tabs, newlines, etc.).
     * \-: Hyphen.
     * \.: Period.
     * \&: Ampersand.
     * \[\]: Square brackets.
     * \(\): Parentheses.
     * \:: Colon.
     * \/: Forward slash.
     * \\\\: Backslash (double escaped for regex).
     *
     * @var string
     */
    public string $patternCity = '/^[\p{L}\p{M}\s\-\.\'\&\[\]\(\):\/\\\\]{1,255}$/u';

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
    public const XML_PATH_SECURITY_PATTERN_CITY_ENABLED = 'system/security_pattern/security_pattern_city';
    
    /**
     * Description of allowed characters for city fields.
     *
     * @var string
     */
    public string $allowedCharsDescription = 'A-Z, a-z, 0-9, -, ., \', &, [, ], (, ), :';

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
     * Check if both the global security pattern and city validation are enabled in the configuration.
     *
     * @return bool
     */
    public function isValidationEnabled(): bool
    {
        $isGlobalPatternEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $isCityValidationEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_PATTERN_CITY_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        return $isGlobalPatternEnabled && $isCityValidationEnabled;
    }

    /**
     * Validate the city value against the pattern.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if ($value === null || $value === '' || !is_string($value)) {
            return true;
        }
    
        return preg_match($this->patternCity, trim($value)) === 1;
        
        return false;
    }
}
