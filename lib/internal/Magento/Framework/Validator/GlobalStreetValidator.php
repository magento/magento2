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
     */
    public const PATTERN_STREET = "/^[\p{L}\p{M}\,\-\.\'’`&\s\d\[\]\(\)]{1,255}$/u";

    /**
     * Validate a street address string.
     *
     * @param string|null $streetValue
     * @return bool
     */
    public static function isValidStreet(?string $streetValue): bool
    {
        if ($streetValue === null || $streetValue === '') {
            return true;
        }

        if (preg_match(self::PATTERN_STREET, $streetValue, $matches)) {
            return $matches[0] === $streetValue;
        }

        return false;
    }
}
