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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Utility class for proxying math function to bcmath functions, if present,
 * otherwise to PHP builtin math operators, with limited detection of overflow conditions.
 * Sampling of PHP environments and platforms suggests that at least 80% to 90% support bcmath.
 * This file should only be loaded for the 10% to 20% lacking access to the bcmath extension.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Locale_Math_PhpMath extends Zend_Locale_Math
{
    public static function disable()
    {
        self::$_bcmathDisabled = true;
        self::$add   = array('Zend_Locale_Math_PhpMath', 'Add');
        self::$sub   = array('Zend_Locale_Math_PhpMath', 'Sub');
        self::$pow   = array('Zend_Locale_Math_PhpMath', 'Pow');
        self::$mul   = array('Zend_Locale_Math_PhpMath', 'Mul');
        self::$div   = array('Zend_Locale_Math_PhpMath', 'Div');
        self::$comp  = array('Zend_Locale_Math_PhpMath', 'Comp');
        self::$sqrt  = array('Zend_Locale_Math_PhpMath', 'Sqrt');
        self::$mod   = array('Zend_Locale_Math_PhpMath', 'Mod');
        self::$scale = array('Zend_Locale_Math_PhpMath', 'Scale');

        self::$defaultScale     = 0;
        self::$defaultPrecision = 1;
    }

    public static $defaultScale;
    public static $defaultPrecision;


    public static function Add($op1, $op2, $scale = null)
    {
        if ($scale === null) {
            $scale     = Zend_Locale_Math_PhpMath::$defaultScale;
            $precision = Zend_Locale_Math_PhpMath::$defaultPrecision;
        } else {
            $precision = pow(10, -$scale);
        }

        if (empty($op1)) {
            $op1 = 0;
        }
        $op1 = self::normalize($op1);
        $op2 = self::normalize($op2);
        $result = $op1 + $op2;
        if (is_infinite($result)  or  (abs($result - $op2 - $op1) > $precision)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("addition overflow: $op1 + $op2 != $result", $op1, $op2, $result);
        }

        return self::round(self::normalize($result), $scale);
    }

    public static function Sub($op1, $op2, $scale = null)
    {
        if ($scale === null) {
            $scale     = Zend_Locale_Math_PhpMath::$defaultScale;
            $precision = Zend_Locale_Math_PhpMath::$defaultPrecision;
        } else {
            $precision = pow(10, -$scale);
        }

        if (empty($op1)) {
            $op1 = 0;
        }
        $op1  = self::normalize($op1);
        $op2  = self::normalize($op2);
        $result = $op1 - $op2;
        if (is_infinite($result)  or  (abs($result + $op2 - $op1) > $precision)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("subtraction overflow: $op1 - $op2 != $result", $op1, $op2, $result);
        }

        return self::round(self::normalize($result), $scale);
    }

    public static function Pow($op1, $op2, $scale = null)
    {
        if ($scale === null) {
            $scale = Zend_Locale_Math_PhpMath::$defaultScale;
        }

        $op1 = self::normalize($op1);
        $op2 = self::normalize($op2);

        // BCMath extension doesn't use decimal part of the power
        // Provide the same behavior
        $op2 = ($op2 > 0) ? floor($op2) : ceil($op2);

        $result = pow($op1, $op2);
        if (is_infinite($result)  or  is_nan($result)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("power overflow: $op1 ^ $op2", $op1, $op2, $result);
        }

        return self::round(self::normalize($result), $scale);
    }

    public static function Mul($op1, $op2, $scale = null)
    {
        if ($scale === null) {
            $scale = Zend_Locale_Math_PhpMath::$defaultScale;
        }

        if (empty($op1)) {
            $op1 = 0;
        }
        $op1 = self::normalize($op1);
        $op2 = self::normalize($op2);
        $result = $op1 * $op2;
        if (is_infinite($result)  or  is_nan($result)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("multiplication overflow: $op1 * $op2 != $result", $op1, $op2, $result);
        }

        return self::round(self::normalize($result), $scale);
    }

    public static function Div($op1, $op2, $scale = null)
    {
        if ($scale === null) {
            $scale = Zend_Locale_Math_PhpMath::$defaultScale;
        }

        if (empty($op2)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("can not divide by zero", $op1, $op2, null);
        }
        if (empty($op1)) {
            $op1 = 0;
        }
        $op1 = self::normalize($op1);
        $op2 = self::normalize($op2);
        $result = $op1 / $op2;
        if (is_infinite($result)  or  is_nan($result)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("division overflow: $op1 / $op2 != $result", $op1, $op2, $result);
        }

        return self::round(self::normalize($result), $scale);
    }

    public static function Sqrt($op1, $scale = null)
    {
        if ($scale === null) {
            $scale = Zend_Locale_Math_PhpMath::$defaultScale;
        }

        if (empty($op1)) {
            $op1 = 0;
        }
        $op1 = self::normalize($op1);
        $result = sqrt($op1);
        if (is_nan($result)) {
            return NULL;
        }

        return self::round(self::normalize($result), $scale);
    }

    public static function Mod($op1, $op2)
    {
        if (empty($op1)) {
            $op1 = 0;
        }
        if (empty($op2)) {
            return NULL;
        }
        $op1 = self::normalize($op1);
        $op2 = self::normalize($op2);
        if ((int)$op2 == 0) {
            return NULL;
        }
        $result = $op1 % $op2;
        if (is_nan($result)  or  (($op1 - $result) % $op2 != 0)) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("modulus calculation error: $op1 % $op2 != $result", $op1, $op2, $result);
        }

        return self::normalize($result);
    }

    public static function Comp($op1, $op2, $scale = null)
    {
        if ($scale === null) {
            $scale     = Zend_Locale_Math_PhpMath::$defaultScale;
        }

        if (empty($op1)) {
            $op1 = 0;
        }
        $op1 = self::normalize($op1);
        $op2 = self::normalize($op2);
        if ($scale <> 0) {
            $op1 = self::round($op1, $scale);
            $op2 = self::round($op2, $scale);
        } else {
            $op1 = ($op1 > 0) ? floor($op1) : ceil($op1);
            $op2 = ($op2 > 0) ? floor($op2) : ceil($op2);
        }
        if ($op1 > $op2) {
            return 1;
        } else if ($op1 < $op2) {
            return -1;
        }
        return 0;
    }

    public static function Scale($scale)
    {
        if ($scale > 9) {
            #require_once 'Zend/Locale/Math/Exception.php';
            throw new Zend_Locale_Math_Exception("can not scale to precision $scale", $scale, null, null);
        }
        self::$defaultScale     = $scale;
        self::$defaultPrecision = pow(10, -$scale);
        return true;
    }
}

Zend_Locale_Math_PhpMath::disable(); // disable use of bcmath functions
