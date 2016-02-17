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
 * @package    Zend_Crypt
 * @subpackage Math
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Crypt_Math_BigInteger_Interface
 */
#require_once 'Zend/Crypt/Math/BigInteger/Interface.php';

/**
 * Support for arbitrary precision mathematics in PHP.
 *
 * Zend_Crypt_Math_BigInteger_Gmp is a wrapper across the PHP BCMath
 * extension.
 *
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Crypt_Math_BigInteger_Gmp implements Zend_Crypt_Math_BigInteger_Interface
{

    /**
     * Initialise a big integer into an extension specific type.
     * @param string $operand
     * @param int $base
     * @return string
     */
    public function init($operand, $base = 10)
    {
        return $operand;
    }

    /**
     * Adds two arbitrary precision numbers
     *
     * @param resource $left_operand
     * @param resource $right_operand
     * @return string
     */
    public function add($left_operand, $right_operand)
    {
        $result = gmp_add($left_operand, $right_operand);
        return gmp_strval($result);
    }

    /**
     * Subtract numbers
     *
     * @param resource $left_operand
     * @param resource $right_operand
     * @return string
     */
    public function subtract($left_operand, $right_operand)
    {
        $result = gmp_sub($left_operand, $right_operand);
        return gmp_strval($result);
    }

    /**
     * Compare two big integers and returns result as an integer where 0 means
     * both are identical, 1 that left_operand is larger, or -1 that
     * right_operand is larger.
     *
     * @param resource $left_operand
     * @param resource $right_operand
     * @return int
     */
    public function compare($left_operand, $right_operand)
    {
        $result = gmp_cmp($left_operand, $right_operand);
        return gmp_strval($result);
    }

    /**
     * Divide two big integers and return result or NULL if the denominator
     * is zero.
     *
     * @param resource $left_operand
     * @param resource $right_operand
     * @return string|null
     */
    public function divide($left_operand, $right_operand)
    {
        $result = gmp_div($left_operand, $right_operand);
        return gmp_strval($result);
    }

    /**
     * Modulo operation
     *
     * @param resource $left_operand
     * @param resource $modulus
     * @internal param string $right_operand
     * @return string
     */
    public function modulus($left_operand, $modulus)
    {
        $result = gmp_mod($left_operand, $modulus);
        return gmp_strval($result);
    }

    /**
     * Multiply numbers
     *
     * @param resource $left_operand
     * @param resource $right_operand
     * @return string
     */
    public function multiply($left_operand, $right_operand)
    {
        $result = gmp_mul($left_operand, $right_operand);
        return gmp_strval($result);
    }

    /**
     * Raise number into power
     *
     * @param resource $left_operand
     * @param int      $right_operand
     * @return string
     */
    public function pow($left_operand, $right_operand)
    {
        $result = gmp_pow($left_operand, $right_operand);
        return gmp_strval($result);
    }

    /**
     * Raise number into power with modulo
     *
     * @param resource $left_operand
     * @param resource $right_operand
     * @param resource $modulus
     * @return string
     */
    public function powmod($left_operand, $right_operand, $modulus)
    {
        $result = gmp_powm($left_operand, $right_operand, $modulus);
        return gmp_strval($result);
    }

    /**
     * Calculate square root
     *
     * @param $operand
     * @return string
     */
    public function sqrt($operand)
    {
        $result = gmp_sqrt($operand);
        return gmp_strval($result);
    }

    /**
     * @param string $operand
     * @return string
     */
    public function binaryToInteger($operand)
    {
        $result = '0';
        while (strlen($operand)) {
            $ord = ord(substr($operand, 0, 1));
            $result = gmp_add(gmp_mul($result, 256), $ord);
            $operand = substr($operand, 1);
        }
        return gmp_strval($result);
    }

    /**
     * @param resource $operand GMP number resource
     * @return string
     */
    public function integerToBinary($operand)
    {
        $bigInt = gmp_strval($operand, 16);
        if (strlen($bigInt) % 2 != 0) {
            $bigInt = '0' . $bigInt;
        } else if ($bigInt[0] > '7') {
            $bigInt = '00' . $bigInt;
        }
        $return = pack("H*", $bigInt);
        return $return;
    }

    /**
     * @param string $operand
     * @return string
     */
    public function hexToDecimal($operand)
    {
        $return = '0';
        while(strlen($hex)) {
            $hex = hexdec(substr($operand, 0, 4));
            $dec = gmp_add(gmp_mul($return, 65536), $hex);
            $operand = substr($operand, 4);
        }
        return $return;
    }

}
