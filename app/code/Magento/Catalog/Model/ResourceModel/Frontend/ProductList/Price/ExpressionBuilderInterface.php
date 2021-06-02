<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

/**
 * Interface ExpressionBuilderInterface
 * @package Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price
 */
interface ExpressionBuilderInterface
{
    /**
     * @param bool $value
     * @return mixed
     */
    public function bool(bool $value);

    /**
     * @param int $value
     * @return mixed
     */
    public function integer(int $value);

    /**
     * @param float $value
     * @return mixed
     */
    public function double(float $value);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function add($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function subtract($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function multiply($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function divide($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function modulo($left, $right);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function negate($expression);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function not($expression);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function and($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function or($left, $right);

    /**
     * @param mixed $expression
     * @param mixed ...$expressions
     * @return mixed
     */
    public function all($expression, ...$expressions);

    /**
     * @param mixed $expression
     * @param mixed ...$expressions
     * @return mixed
     */
    public function any($expression, ...$expressions);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function equalTo($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function greaterThan($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function greaterThanOrEqualTo($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function lesserThan($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function lesserThanOrEqualTo($left, $right);

    /**
     * @param mixed $condition
     * @param mixed $true
     * @param mixed $false
     * @return mixed
     */
    public function ifThenElse($condition, $true, $false);

    /**
     * @param mixed $expression
     * @param array $branches
     * @param mixed $default
     * @return mixed
     */
    public function caseOf($expression, array $branches, $default);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function abs($expression);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function ceil($expression);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function floor($expression);

    /**
     * @param mixed $expression
     * @param int $digits
     * @return mixed
     */
    public function round($expression, int $digits);

    /**
     * @param mixed $expression
     * @param mixed ...$expressions
     * @return mixed
     */
    public function min($expression, ...$expressions);

    /**
     * @param mixed $expression
     * @param mixed ...$expressions
     * @return mixed
     */
    public function max($expression, ...$expressions);

    /**
     * @param mixed $base
     * @param mixed $exponent
     * @return mixed
     */
    public function pow($base, $exponent);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function sqrt($expression);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function exp($expression);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function ln($expression);

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function log10($expression);

    /**
     * @param mixed $base
     * @param mixed $expression
     * @return mixed
     */
    public function logn($base, $expression);

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function attributeValue(string $attributeCode);
}
