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
     * Regular expression pattern for validating names.
     */
    public const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'’`&\s\d]){1,255}+/u';

    /**
     * Validate a name string.
     *
     * @param string|null $nameValue
     * @return bool
     */
    public static function isValidName(?string $nameValue): bool
    {
        if ($nameValue === null || $nameValue === '') {
            return true;
        }
    
        if (preg_match(self::PATTERN_NAME, $nameValue, $matches)) {
            return $matches[0] === $nameValue;
        }
    
        return false;
    }
}
