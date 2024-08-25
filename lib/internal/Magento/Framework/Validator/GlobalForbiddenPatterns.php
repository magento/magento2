<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

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
    const XML_PATH_SECURITY_REGEX_ENABLED = 'system/security/security_regex_enabled';
    
    /**
     * Returns an array of forbidden patterns.
     *
     * @return string[]
     */
    public static function getPatterns(): array
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
     * @param string|null $fieldValue
     * @return bool
     */
    public static function isValid(mixed $fieldValue): bool
    {
        if ($fieldValue === null || $fieldValue === '' || !is_string($fieldValue)) {
            return true;
        }

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
}
