<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GlobalForbiddenPatterns
 * Provides a set of forbidden patterns used for validation across the application.
 */
class GlobalForbiddenPatterns
{
    /**
     * XML path for regex validation.
     *
     * @var string
     */
    public const XML_PATH_SECURITY_REGEX_ENABLED = 'system/security/security_regex_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

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
     * Returns an array of forbidden patterns.
     *
     * @return string[]
     */
    public function getPatterns(): array
    {
        return [
            '/{{.*}}/',
            '/<\?=/',
            '/<\?php/',
            '/shell_exec/',
            '/eval\(/',
            '/\${IFS%/',
            '/\bcurl\b/',
        ];
    }

    /**
     * Checks if the given field value is valid according to the forbidden patterns.
     *
     * @param mixed $fieldValue
     * @return bool
     */
    public function isValid(mixed $fieldValue): bool
    {
        if ($fieldValue === null || $fieldValue === '' || !is_string($fieldValue)) {
            return true;
        }

        $fieldValue = trim($fieldValue);
        
        foreach (self::getPatterns() as $pattern) {
            if (preg_match($pattern, $fieldValue)) {
                return false;
            }
        }

        // Check if the field contains a base64 encoded string and decode it for further validation
        if (preg_match('/base64_decode\(/', $fieldValue)) {
            $decodedValue = base64_decode($fieldValue);
            // Recursively check the decoded value
            return self::isValid($decodedValue);
        }

        return true;
    }

    /**
     * Validate all fields in the provided data array based on forbidden patterns.
     *
     * @param array $data              The data array to be validated.
     * @param array &$validationErrors An array to collect validation errors.
     * @return void
     */
    public function validateData(
        array $data,
        array &$validationErrors
    ): void {
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SECURITY_REGEX_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    
        if ($isRegexEnabled) {
            foreach ($data as $key => $value) {
                if (is_string($value) && !$this->isValid($value)) {
                    $validationErrors[] = __("Field %1 contains invalid characters.", $key);
                }
            }
        }
    }
}
