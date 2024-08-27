<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

class GlobalCityValidator
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
     */
    private const PATTERN_CITY = '/^[\p{L}\p{M}\s\-\.\'\&\[\]\(\):]{1,100}$/u';

    /**
     * Validate a city name string.
     *
     * @param string|null $cityValue
     * @return bool
     */
    public static function isValidCity(mixed $cityValue): bool
    {
        if ($cityValue === null || $cityValue === '' || !is_string($cityValue)) {
            return true;
        }

        if (preg_match(self::PATTERN_CITY, trim($cityValue), $matches)) {
            return $matches[0] === trim($cityValue);
        }

        return false;
    }
}
