<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Math\BigInteger\Adapter;

interface AdapterInterface
{
    /**
     * Base62 alphabet for arbitrary base conversion
     */
    const BASE62_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Create adapter-specific representation of a big integer
     *
     * @param  string $operand
     * @param  int|null $base
     * @return mixed
     */
    public function init($operand, $base = null);

    /**
     * Add two big integers
     *
     * @param  string $leftOperand
     * @param  string $rightOperand
     * @return string
     */
    public function add($leftOperand, $rightOperand);

    /**
     * Subtract two big integers
     *
     * @param  string $leftOperand
     * @param  string $rightOperand
     * @return string
     */
    public function sub($leftOperand, $rightOperand);

    /**
     * Multiply two big integers
     *
     * @param  string $leftOperand
     * @param  string $rightOperand
     * @return string
     */
    public function mul($leftOperand, $rightOperand);

    /**
     * Divide two big integers
     * (this method returns only int part of result)
     *
     * @param  string $leftOperand
     * @param  string $rightOperand
     * @return string
     */
    public function div($leftOperand, $rightOperand);

    /**
     * Raise a big integers to another
     *
     * @param  string $operand
     * @param  string $exp
     * @return string
     */
    public function pow($operand, $exp);

    /**
     * Get the square root of a big integer
     *
     * @param  string $operand
     * @return string
     */
    public function sqrt($operand);

    /**
     * Get absolute value of a big integer
     *
     * @param  string $operand
     * @return string
     */
    public function abs($operand);

    /**
     * Get modulus of a big integer
     *
     * @param  string $leftOperand
     * @param  string $modulus
     * @return string
     */
    public function mod($leftOperand, $modulus);

    /**
     * Raise a big integer to another, reduced by a specified modulus
     *
     * @param  string $leftOperand
     * @param  string $rightOperand
     * @param  string $modulus
     * @return string
     */
    public function powmod($leftOperand, $rightOperand, $modulus);

    /**
     * Compare two big integers
     * Returns < 0 if leftOperand is less than rightOperand;
     * > 0 if leftOperand is greater than rightOperand, and 0 if they are equal.
     *
     * @param  string $leftOperand
     * @param  string $rightOperand
     * @return int
     */
    public function comp($leftOperand, $rightOperand);

    /**
     * Convert big integer into it's binary number representation
     *
     * @param  string $int
     * @param  bool $twoc
     * @return string
     */
    public function intToBin($int, $twoc = false);

    /**
     * Convert binary number into big integer
     *
     * @param  string $bytes
     * @param  bool $twoc
     * @return string
     */
    public function binToInt($bytes, $twoc = false);

    /**
     * Convert a number between arbitrary bases
     *
     * @param  string $operand
     * @param  int $fromBase
     * @param  int $toBase
     * @return string
     */
    public function baseConvert($operand, $fromBase, $toBase = 10);
}
