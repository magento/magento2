<?
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

class GlobalCityValidator
{
    /**
     * Regular expression pattern for validating city names.
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
     */
    public const PATTERN_CITY = '/^[\p{L}\p{M}\s\-\.\'\&\[\]\(\)]{1,100}$/u';

    /**
     * Validate a city name string.
     *
     * @param string|null $cityValue
     * @return bool
     */
    public static function isValidCity(?string $cityValue): bool
    {
        if ($cityValue === null || $cityValue === '') {
            return true;
        }

        if (preg_match(self::PATTERN_CITY, $cityValue, $matches)) {
            return $matches[0] === $cityValue;
        }

        return false;
    }
}
