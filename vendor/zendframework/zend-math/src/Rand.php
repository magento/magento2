<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Math;

use RandomLib;

/**
 * Pseudorandom number generator (PRNG)
 */
abstract class Rand
{
    /**
     * Alternative random byte generator using RandomLib
     *
     * @var RandomLib\Generator
     */
    protected static $generator = null;

    /**
     * Generate random bytes using OpenSSL or Mcrypt and mt_rand() as fallback
     *
     * @param  int $length
     * @param  bool $strong true if you need a strong random generator (cryptography)
     * @return string
     * @throws Exception\RuntimeException
     */
    public static function getBytes($length, $strong = false)
    {
        $length = (int) $length;

        if ($length <= 0) {
            return false;
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $usable);
            if (true === $usable) {
                return $bytes;
            }
        }
        if (function_exists('mcrypt_create_iv')) {
            $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if ($bytes !== false && strlen($bytes) === $length) {
                return $bytes;
            }
        }
        $checkAlternatives = (file_exists('/dev/urandom') && is_readable('/dev/urandom'))
            || class_exists('\\COM', false);
        if (true === $strong && false === $checkAlternatives) {
            throw new Exception\RuntimeException(
                'This PHP environment doesn\'t support secure random number generation. ' .
                'Please consider installing the OpenSSL and/or Mcrypt extensions'
            );
        }
        $generator = self::getAlternativeGenerator();
        return $generator->generate($length);
    }

    /**
     * Retrieve a fallback/alternative RNG generator
     *
     * @return RandomLib\Generator
     */
    public static function getAlternativeGenerator()
    {
        if (null !== static::$generator) {
            return static::$generator;
        }
        if (!class_exists('RandomLib\\Factory')) {
            throw new Exception\RuntimeException(
                'The RandomLib fallback pseudorandom number generator (PRNG) '
                . ' must be installed in the absence of the OpenSSL and '
                . 'Mcrypt extensions'
            );
        }
        $factory = new RandomLib\Factory;
        $factory->registerSource(
            'HashTiming',
            'Zend\Math\Source\HashTiming'
        );
        static::$generator = $factory->getMediumStrengthGenerator();
        return static::$generator;
    }

    /**
     * Generate random boolean
     *
     * @param  bool $strong true if you need a strong random generator (cryptography)
     * @return bool
     */
    public static function getBoolean($strong = false)
    {
        $byte = static::getBytes(1, $strong);
        return (bool) (ord($byte) % 2);
    }

    /**
     * Generate a random integer between $min and $max
     *
     * @param  int $min
     * @param  int $max
     * @param  bool $strong true if you need a strong random generator (cryptography)
     * @return int
     * @throws Exception\DomainException
     */
    public static function getInteger($min, $max, $strong = false)
    {
        if ($min > $max) {
            throw new Exception\DomainException(
                'The min parameter must be lower than max parameter'
            );
        }
        $range = $max - $min;
        if ($range == 0) {
            return $max;
        } elseif ($range > PHP_INT_MAX || is_float($range)) {
            throw new Exception\DomainException(
                'The supplied range is too great to generate'
            );
        }

        // calculate number of bits required to store range on this machine
        $r = $range;
        $bits = 0;
        while ($r) {
            $bits++;
            $r >>= 1;
        }

        $bits   = (int) max($bits, 1);
        $bytes  = (int) max(ceil($bits / 8), 1);
        $filter = (int) ((1 << $bits) - 1);

        do {
            $rnd  = hexdec(bin2hex(static::getBytes($bytes, $strong)));
            $rnd &= $filter;
        } while ($rnd > $range);

        return ($min + $rnd);
    }

    /**
     * Generate random float (0..1)
     * This function generates floats with platform-dependent precision
     *
     * PHP uses double precision floating-point format (64-bit) which has
     * 52-bits of significand precision. We gather 7 bytes of random data,
     * and we fix the exponent to the bias (1023). In this way we generate
     * a float of 1.mantissa.
     *
     * @param  bool $strong  true if you need a strong random generator (cryptography)
     * @return float
     */
    public static function getFloat($strong = false)
    {
        $bytes    = static::getBytes(7, $strong);
        $bytes[6] = $bytes[6] | chr(0xF0);
        $bytes   .= chr(63); // exponent bias (1023)
        list(, $float) = unpack('d', $bytes);

        return ($float - 1);
    }

    /**
     * Generate a random string of specified length.
     *
     * Uses supplied character list for generating the new string.
     * If no character list provided - uses Base 64 character set.
     *
     * @param  int $length
     * @param  string|null $charlist
     * @param  bool $strong  true if you need a strong random generator (cryptography)
     * @return string
     * @throws Exception\DomainException
     */
    public static function getString($length, $charlist = null, $strong = false)
    {
        if ($length < 1) {
            throw new Exception\DomainException('Length should be >= 1');
        }

        // charlist is empty or not provided
        if (empty($charlist)) {
            $numBytes = ceil($length * 0.75);
            $bytes    = static::getBytes($numBytes, $strong);
            return substr(rtrim(base64_encode($bytes), '='), 0, $length);
        }

        $listLen = strlen($charlist);

        if ($listLen == 1) {
            return str_repeat($charlist, $length);
        }

        $bytes  = static::getBytes($length, $strong);
        $pos    = 0;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $pos     = ($pos + ord($bytes[$i])) % $listLen;
            $result .= $charlist[$pos];
        }

        return $result;
    }
}
