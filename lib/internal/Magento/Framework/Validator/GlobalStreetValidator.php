<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

class GlobalStreetValidator
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
     */
    private const PATTERN_STREET = "/^[\p{L}\p{M}\,\-\.\'’`&\s\d\[\]\(\)]{1,255}$/u";
    
    /**
     * Validate a street address string.
     *
     * @param string|null $streetValue
     * @return bool
     */
    public function isValidStreet(mixed $streetValue): bool
    {
        if ($streetValue === null || $streetValue === '' || !is_string($streetValue)) {
            return true;
        }

        $streetValue = trim($streetValue);
        if (preg_match(self::PATTERN_STREET, $streetValue, $matches)) {
            return $matches[0] === $streetValue;
        }

        return false;
    }
}
