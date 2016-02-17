<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt;

/**
 * Tools for cryptography
 */
class Utils
{
    /**
     * Compare two strings to avoid timing attacks
     *
     * C function memcmp() internally used by PHP, exits as soon as a difference
     * is found in the two buffers. That makes possible of leaking
     * timing information useful to an attacker attempting to iteratively guess
     * the unknown string (e.g. password).
     * The length will leak.
     *
     * @param  string $expected
     * @param  string $actual
     * @return bool
     */
    public static function compareStrings($expected, $actual)
    {
        $expected     = (string) $expected;
        $actual       = (string) $actual;

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        $lenExpected  = strlen($expected);
        $lenActual    = strlen($actual);
        $len          = min($lenExpected, $lenActual);

        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($expected[$i]) ^ ord($actual[$i]);
        }
        $result |= $lenExpected ^ $lenActual;

        return ($result === 0);
    }
}
