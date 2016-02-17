<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Key\Derivation;

/**
 * Scrypt key derivation function
 *
 * @see      http://www.tarsnap.com/scrypt.html
 * @see      https://tools.ietf.org/html/draft-josefsson-scrypt-kdf-01
 */
abstract class Scrypt
{
    /**
     * Execute the scrypt algorithm
     *
     * @param  string $password
     * @param  string $salt
     * @param  int $n CPU cost
     * @param  int $r Memory cost
     * @param  int $p parallelization cost
     * @param  int $length size of the output key
     * @return string
     */
    public static function calc($password, $salt, $n, $r, $p, $length)
    {
        if ($n == 0 || ($n & ($n - 1)) != 0) {
            throw new Exception\InvalidArgumentException("N must be > 0 and a power of 2");
        }
        if ($n > PHP_INT_MAX / 128 / $r) {
            throw new Exception\InvalidArgumentException("Parameter n is too large");
        }
        if ($r > PHP_INT_MAX / 128 / $p) {
            throw new Exception\InvalidArgumentException("Parameter r is too large");
        }

        if (extension_loaded('Scrypt')) {
            if ($length < 16) {
                throw new Exception\InvalidArgumentException("Key length is too low, must be greater or equal to 16");
            }
            return self::hex2bin(scrypt($password, $salt, $n, $r, $p, $length));
        }

        $b = Pbkdf2::calc('sha256', $password, $salt, 1, $p * 128 * $r);

        $s = '';
        for ($i = 0; $i < $p; $i++) {
            $s .= self::scryptROMix(substr($b, $i * 128 * $r, 128 * $r), $n, $r);
        }

        return Pbkdf2::calc('sha256', $password, $s, 1, $length);
    }

   /**
    * scryptROMix
    *
    * @param  string $b
    * @param  int $n
    * @param  int $r
    * @return string
    * @see    https://tools.ietf.org/html/draft-josefsson-scrypt-kdf-01#section-4
    */
    protected static function scryptROMix($b, $n, $r)
    {
        $x = $b;
        $v = array();
        for ($i = 0; $i < $n; $i++) {
            $v[$i] = $x;
            $x = self::scryptBlockMix($x, $r);
        }
        for ($i = 0; $i < $n; $i++) {
            $j = self::integerify($x) % $n;
            $t = $x ^  $v[$j];
            $x = self::scryptBlockMix($t, $r);
        }
        return $x;
    }

    /**
     * scryptBlockMix
     *
     * @param  string $b
     * @param  int $r
     * @return string
     * @see    https://tools.ietf.org/html/draft-josefsson-scrypt-kdf-01#section-3
     */
    protected static function scryptBlockMix($b, $r)
    {
        $x    = substr($b, -64);
        $even = '';
        $odd  = '';
        $len  = 2 * $r;

        for ($i = 0; $i < $len; $i++) {
            if (PHP_INT_SIZE === 4) {
                $x = self::salsa208Core32($x ^ substr($b, 64 * $i, 64));
            } else {
                $x = self::salsa208Core64($x ^ substr($b, 64 * $i, 64));
            }
            if ($i % 2 == 0) {
                $even .= $x;
            } else {
                $odd .= $x;
            }
        }
        return $even . $odd;
    }

    /**
     * Salsa 20/8 core (32 bit version)
     *
     * @param  string $b
     * @return string
     * @see    https://tools.ietf.org/html/draft-josefsson-scrypt-kdf-01#section-2
     * @see    http://cr.yp.to/salsa20.html
     */
    protected static function salsa208Core32($b)
    {
        $b32 = array();
        for ($i = 0; $i < 16; $i++) {
            list(, $b32[$i]) = unpack("V", substr($b, $i * 4, 4));
        }

        $x = $b32;
        for ($i = 0; $i < 8; $i += 2) {
            $a      = ($x[ 0] + $x[12]);
            $x[ 4] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[ 4] + $x[ 0]);
            $x[ 8] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[ 8] + $x[ 4]);
            $x[12] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[12] + $x[ 8]);
            $x[ 0] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[ 5] + $x[ 1]);
            $x[ 9] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[ 9] + $x[ 5]);
            $x[13] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[13] + $x[ 9]);
            $x[ 1] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[ 1] + $x[13]);
            $x[ 5] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[10] + $x[ 6]);
            $x[14] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[14] + $x[10]);
            $x[ 2] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[ 2] + $x[14]);
            $x[ 6] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[ 6] + $x[ 2]);
            $x[10] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[15] + $x[11]);
            $x[ 3] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[ 3] + $x[15]);
            $x[ 7] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[ 7] + $x[ 3]);
            $x[11] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[11] + $x[ 7]);
            $x[15] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[ 0] + $x[ 3]);
            $x[ 1] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[ 1] + $x[ 0]);
            $x[ 2] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[ 2] + $x[ 1]);
            $x[ 3] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[ 3] + $x[ 2]);
            $x[ 0] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[ 5] + $x[ 4]);
            $x[ 6] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[ 6] + $x[ 5]);
            $x[ 7] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[ 7] + $x[ 6]);
            $x[ 4] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[ 4] + $x[ 7]);
            $x[ 5] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[10] + $x[ 9]);
            $x[11] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[11] + $x[10]);
            $x[ 8] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[ 8] + $x[11]);
            $x[ 9] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[ 9] + $x[ 8]);
            $x[10] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
            $a      = ($x[15] + $x[14]);
            $x[12] ^= ($a << 7) | ($a >> 25) & 0x7f;
            $a      = ($x[12] + $x[15]);
            $x[13] ^= ($a << 9) | ($a >> 23) & 0x1ff;
            $a      = ($x[13] + $x[12]);
            $x[14] ^= ($a << 13) | ($a >> 19) & 0x1fff;
            $a      = ($x[14] + $x[13]);
            $x[15] ^= ($a << 18) | ($a >> 14) & 0x3ffff;
        }
        for ($i = 0; $i < 16; $i++) {
            $b32[$i] = $b32[$i] + $x[$i];
        }
        $result = '';
        for ($i = 0; $i < 16; $i++) {
            $result .= pack("V", $b32[$i]);
        }

        return $result;
    }

    /**
     * Salsa 20/8 core (64 bit version)
     *
     * @param  string $b
     * @return string
     * @see    https://tools.ietf.org/html/draft-josefsson-scrypt-kdf-01#section-2
     * @see    http://cr.yp.to/salsa20.html
     */
    protected static function salsa208Core64($b)
    {
        $b32 = array();
        for ($i = 0; $i < 16; $i++) {
            list(, $b32[$i]) = unpack("V", substr($b, $i * 4, 4));
        }

        $x = $b32;
        for ($i = 0; $i < 8; $i += 2) {
            $a      = ($x[ 0] + $x[12]) & 0xffffffff;
            $x[ 4] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[ 4] + $x[ 0]) & 0xffffffff;
            $x[ 8] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[ 8] + $x[ 4]) & 0xffffffff;
            $x[12] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[12] + $x[ 8]) & 0xffffffff;
            $x[ 0] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[ 5] + $x[ 1]) & 0xffffffff;
            $x[ 9] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[ 9] + $x[ 5]) & 0xffffffff;
            $x[13] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[13] + $x[ 9]) & 0xffffffff;
            $x[ 1] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[ 1] + $x[13]) & 0xffffffff;
            $x[ 5] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[10] + $x[ 6]) & 0xffffffff;
            $x[14] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[14] + $x[10]) & 0xffffffff;
            $x[ 2] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[ 2] + $x[14]) & 0xffffffff;
            $x[ 6] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[ 6] + $x[ 2]) & 0xffffffff;
            $x[10] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[15] + $x[11]) & 0xffffffff;
            $x[ 3] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[ 3] + $x[15]) & 0xffffffff;
            $x[ 7] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[ 7] + $x[ 3]) & 0xffffffff;
            $x[11] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[11] + $x[ 7]) & 0xffffffff;
            $x[15] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[ 0] + $x[ 3]) & 0xffffffff;
            $x[ 1] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[ 1] + $x[ 0]) & 0xffffffff;
            $x[ 2] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[ 2] + $x[ 1]) & 0xffffffff;
            $x[ 3] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[ 3] + $x[ 2]) & 0xffffffff;
            $x[ 0] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[ 5] + $x[ 4]) & 0xffffffff;
            $x[ 6] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[ 6] + $x[ 5]) & 0xffffffff;
            $x[ 7] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[ 7] + $x[ 6]) & 0xffffffff;
            $x[ 4] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[ 4] + $x[ 7]) & 0xffffffff;
            $x[ 5] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[10] + $x[ 9]) & 0xffffffff;
            $x[11] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[11] + $x[10]) & 0xffffffff;
            $x[ 8] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[ 8] + $x[11]) & 0xffffffff;
            $x[ 9] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[ 9] + $x[ 8]) & 0xffffffff;
            $x[10] ^= ($a << 18) | ($a >> 14);
            $a      = ($x[15] + $x[14]) & 0xffffffff;
            $x[12] ^= ($a << 7) | ($a >> 25);
            $a      = ($x[12] + $x[15]) & 0xffffffff;
            $x[13] ^= ($a << 9) | ($a >> 23);
            $a      = ($x[13] + $x[12]) & 0xffffffff;
            $x[14] ^= ($a << 13) | ($a >> 19);
            $a      = ($x[14] + $x[13]) & 0xffffffff;
            $x[15] ^= ($a << 18) | ($a >> 14);
        }
        for ($i = 0; $i < 16; $i++) {
            $b32[$i] = ($b32[$i] + $x[$i]) & 0xffffffff;
        }
        $result = '';
        for ($i = 0; $i < 16; $i++) {
            $result .= pack("V", $b32[$i]);
        }

        return $result;
    }

    /**
     * Integerify
     *
     * Integerify (B[0] ... B[2 * r - 1]) is defined as the result
     * of interpreting B[2 * r - 1] as a little-endian integer.
     * Each block B is a string of 64 bytes.
     *
     * @param  string $b
     * @return int
     * @see    https://tools.ietf.org/html/draft-josefsson-scrypt-kdf-01#section-4
     */
    protected static function integerify($b)
    {
        $v = 'v';
        if (PHP_INT_SIZE === 8) {
            $v = 'V';
        }
        list(, $n) = unpack($v, substr($b, -64));
        return $n;
    }

    /**
     * Convert hex string in a binary string
     *
     * @param  string $hex
     * @return string
     */
    protected static function hex2bin($hex)
    {
        if (PHP_VERSION_ID >= 50400) {
            return hex2bin($hex);
        }
        $len    = strlen($hex);
        $result = '';
        for ($i = 0; $i < $len; $i+=2) {
            $result .= chr(hexdec($hex[$i] . $hex[$i+1]));
        }
        return $result;
    }
}
