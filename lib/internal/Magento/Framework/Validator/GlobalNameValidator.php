<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

class GlobalNameValidator
{
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
    public const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'’`&\s\d]){1,255}+/u';

    /**
     * Validate a name string.
     *
     * @param string|null $nameValue
     * @return bool
     */
    public function isValidName(mixed $nameValue): bool
    {
        if ($nameValue === null || $nameValue === '' || !is_string($nameValue)) {
            return true;
        }
    
        if (preg_match(self::PATTERN_NAME, trim($nameValue), $matches)) {
            return $matches[0] === trim($nameValue);
        }
    
        return false;
    }
}
