<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Math.php 21180 2010-02-23 22:00:29Z matthew $
 */


/**
 * Utility class for proxying math function to bcmath functions, if present,
 * otherwise to PHP builtin math operators, with limited detection of overflow conditions.
 * Sampling of PHP environments and platforms suggests that at least 80% to 90% support bcmath.
 * Thus, this file should be as light as possible.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Locale_Math
{
    // support unit testing without using bcmath functions
    public static $_bcmathDisabled = false;

    public static $add   = array('Zend_Locale_Math', 'Add');
    public static $sub   = array('Zend_Locale_Math', 'Sub');
    public static $pow   = array('Zend_Locale_Math', 'Pow');
    public static $mul   = array('Zend_Locale_Math', 'Mul');
    public static $div   = array('Zend_Locale_Math', 'Div');
    public static $comp  = array('Zend_Locale_Math', 'Comp');
    public static $sqrt  = array('Zend_Locale_Math', 'Sqrt');
    public static $mod   = array('Zend_Locale_Math', 'Mod');
    public static $scale = 'bcscale';

    public static function isBcmathDisabled()
    {
        return self::$_bcmathDisabled;
    }

    /**
     * Surprisingly, the results of this implementation of round()
     * prove better than the native PHP round(). For example, try:
     *   round(639.795, 2);
     *   round(267.835, 2);
     *   round(0.302515, 5);
     *   round(0.36665, 4);
     * then try:
     *   Zend_Locale_Math::round('639.795', 2);
     */
    public static function round($op1, $precision = 0)
    {
        if (self::$_bcmathDisabled) {
            $op1 = round($op1, $precision);
            if (strpos((string) $op1, 'E') === false) {
                return self::normalize(round($op1, $precision));
            }
        }

        if (strpos($op1, 'E') !== false) {
            $op1 = self::floatalize($op1);
        }

        $op1    = trim(self::normalize($op1));
        $length = strlen($op1);
        if (($decPos = strpos($op1, '.')) === false) {
            $op1 .= '.0';
            $decPos = $length;
            $length += 2;
        }
        if ($precision < 0 && abs($precision) > $decPos) {
            return '0';
        }

        $digitsBeforeDot = $length - ($decPos + 1);
        if ($precision >= ($length - ($decPos + 1))) {
            return $op1;
        }

        if ($precision === 0) {
            $triggerPos = 1;
            $roundPos   = -1;
        } elseif ($precision > 0) {
            $triggerPos = $precision + 1;
            $roundPos   = $precision;
        } else {
            $triggerPos = $precision;
            $roundPos   = $precision -1;
        }

        $triggerDigit = $op1[$triggerPos + $decPos];
        if ($precision < 0) {
            // zero fill digits to the left of the decimal place
            $op1 = substr($op1, 0, $decPos + $precision) . str_pad('', abs($precision), '0');
        }

        if ($triggerDigit >= '5') {
            if ($roundPos + $decPos == -1) {
                return str_pad('1', $decPos + 1, '0');
            }

            $roundUp = str_pad('', $length, '0');
            $roundUp[$decPos] = '.';
            $roundUp[$roundPos + $decPos] = '1';

            if ($op1 > 0) {
                if (self::$_bcmathDisabled) {
                    return Zend_Locale_Math_PhpMath::Add($op1, $roundUp, $precision);
                }
                return self::Add($op1, $roundUp, $precision);
            } else {
                if (self::$_bcmathDisabled) {
                    return Zend_Locale_Math_PhpMath::Sub($op1, $roundUp, $precision);
                }
                return self::Sub($op1, $roundUp, $precision);
            }
        } elseif ($precision >= 0) {
            return substr($op1, 0, $decPos + ($precision ? $precision + 1: 0));
        }

        return (string) $op1;
    }

    /**
     * Convert a scientific notation to float
     * Additionally fixed a problem with PHP <= 5.2.x with big integers
     *
     * @param string $value
     */
    public static function floatalize($value)
    {
        $value = strtoupper($value);
        if (strpos($value, 'E') === false) {
            return $value;
        }

        $number = substr($value, 0, strpos($value, 'E'));
        if (strpos($number, '.') !== false) {
            $post   = strlen(substr($number, strpos($number, '.') + 1));
            $mantis = substr($value, strpos($value, 'E') + 1);
            if ($mantis < 0) {
                $post += abs((int) $mantis);
            }

            $value = number_format($value, $post, '.', '');
        } else {
            $value = number_format($value, 0, '.', '');
        }

        return $value;
    }

    /**
     * Normalizes an input to standard english notation
     * Fixes a problem of BCMath with setLocale which is PHP related
     *
     * @param   integer  $value  Value to normalize
     * @return  string           Normalized string without BCMath problems
     */
    public static function normalize($value)
    {
        $convert = localeconv();
        $value = str_replace($convert['thousands_sep'], "",(string) $value);
        $value = str_replace($convert['positive_sign'], "", $value);
        $value = str_replace($convert['decimal_point'], ".",$value);
        if (!empty($convert['negative_sign']) and (strpos($value, $convert['negative_sign']))) {
            $value = str_replace($convert['negative_sign'], "", $value);
            $value = "-" . $value;
        }

        return $value;
    }

    /**
     * Localizes an input from standard english notation
     * Fixes a problem of BCMath with setLocale which is PHP related
     *
     * @param   integer  $value  Value to normalize
     * @return  string           Normalized string without BCMath problems
     */
    public static function localize($value)
    {
        $convert = localeconv();
        $value = str_replace(".", $convert['decimal_point'], (string) $value);
        if (!empty($convert['negative_sign']) and (strpos($value, "-"))) {
            $value = str_replace("-", $convert['negative_sign'], $value);
        }
        return $value;
    }

    /**
     * Changes exponential numbers to plain string numbers
     * Fixes a problem of BCMath with numbers containing exponents
     *
     * @param integer $value Value to erase the exponent
     * @param integer $scale (Optional) Scale to use
     * @return string
     */
    public static function exponent($value, $scale = null)
    {
        if (!extension_loaded('bcmath')) {
            return $value;
        }

        $split = explode('e', $value);
        if (count($split) == 1) {
            $split = explode('E', $value);
        }

        if (count($split) > 1) {
            $value = bcmul($split[0], bcpow(10, $split[1], $scale), $scale);
        }

        return $value;
    }

    /**
     * BCAdd - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @param  integer $scale
     * @return string
     */
    public static function Add($op1, $op2, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        $op2 = self::exponent($op2, $scale);

        return bcadd($op1, $op2, $scale);
    }

    /**
     * BCSub - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @param  integer $scale
     * @return string
     */
    public static function Sub($op1, $op2, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        $op2 = self::exponent($op2, $scale);
        return bcsub($op1, $op2, $scale);
    }

    /**
     * BCPow - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @param  integer $scale
     * @return string
     */
    public static function Pow($op1, $op2, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        $op2 = self::exponent($op2, $scale);
        return bcpow($op1, $op2, $scale);
    }

    /**
     * BCMul - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @param  integer $scale
     * @return string
     */
    public static function Mul($op1, $op2, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        $op2 = self::exponent($op2, $scale);
        return bcmul($op1, $op2, $scale);
    }

    /**
     * BCDiv - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @param  integer $scale
     * @return string
     */
    public static function Div($op1, $op2, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        $op2 = self::exponent($op2, $scale);
        return bcdiv($op1, $op2, $scale);
    }

    /**
     * BCSqrt - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  integer $scale
     * @return string
     */
    public static function Sqrt($op1, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        return bcsqrt($op1, $scale);
    }

    /**
     * BCMod - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @return string
     */
    public static function Mod($op1, $op2)
    {
        $op1 = self::exponent($op1);
        $op2 = self::exponent($op2);
        return bcmod($op1, $op2);
    }

    /**
     * BCComp - fixes a problem of BCMath and exponential numbers
     *
     * @param  string  $op1
     * @param  string  $op2
     * @param  integer $scale
     * @return string
     */
    public static function Comp($op1, $op2, $scale = null)
    {
        $op1 = self::exponent($op1, $scale);
        $op2 = self::exponent($op2, $scale);
        return bccomp($op1, $op2, $scale);
    }
}

if (!extension_loaded('bcmath')
    || (defined('TESTS_ZEND_LOCALE_BCMATH_ENABLED') && !TESTS_ZEND_LOCALE_BCMATH_ENABLED)
) {
    require_once 'Zend/Locale/Math/PhpMath.php';
    Zend_Locale_Math_PhpMath::disable();
}
