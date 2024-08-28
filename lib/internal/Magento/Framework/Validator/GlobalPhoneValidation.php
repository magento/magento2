<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

class GlobalPhoneValidation
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
     * The pattern ensures that a phone number can be between 1 and 30 characters long.
     */
    public const PATTERN_TELEPHONE = '/(?:[\d\s\+\-\()\/]{1,30})/u';

    /**
     * Validate a phone number string.
     *
     * @param string|null $phoneValue
     * @return bool
     */
    public function isValidPhone(mixed $phoneValue): bool
    {
        if ($phoneValue === null || $phoneValue === '' || !is_string($phoneValue)) {
            return true;
        }

        if (preg_match(self::PATTERN_TELEPHONE, trim($phoneValue), $matches)) {
            return $matches[0] === trim($phoneValue);
        }
    
        return false;
    }
}
