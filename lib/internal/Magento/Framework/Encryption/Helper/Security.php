<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Encryption\Helper;

/**
 * @api
 * @since 100.0.2
 */
class Security
{
    /**
     * Compare two strings in a secure way that avoids string length guessing based on duration of calculation
     *
     * @param string $expected
     * @param string $actual
     * @return bool
     */
    public static function compareStrings($expected, $actual)
    {
        $expected     = (string) $expected;
        $actual       = (string) $actual;

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        $lenExpected  = mb_strlen($expected, '8bit');
        $lenActual    = mb_strlen($actual, '8bit');
        $len          = min($lenExpected, $lenActual);

        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($expected[$i]) ^ ord($actual[$i]);
        }
        $result |= $lenExpected ^ $lenActual;

        return ($result === 0);
    }
}
