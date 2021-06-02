<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

use Magento\Catalog\Model\ResourceModel;
use Magento\Framework\DB\Adapter\AdapterInterface;

class SqlExpressionBuilder implements ExpressionBuilderInterface
{
    /**
     * @var ResourceModel\ProductFactory
     */
    private $productResourceFactory;

    /**
     * @var ResourceModel\Product|null
     */
    private $productResource = null;

    /**
     * @var callable
     */
    private $attributeCallback;

    /**
     * @param ResourceModel\ProductFactory $productResourceFactory
     * @param callable $attributeCallback
     */
    public function __construct(ResourceModel\ProductFactory $productResourceFactory, callable $attributeCallback)
    {
        $this->productResourceFactory = $productResourceFactory;
        $this->attributeCallback = $attributeCallback;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->productResource) {
            $this->productResource = $this->productResourceFactory->create();
        }

        return $this->productResource->getConnection();
    }

    public function bool(bool $value): string
    {
        return $value ? '1' : '0';
    }

    public function integer(int $value): string
    {
        return (string) $value;
    }

    public function double(float $value): string
    {
        return (string) $value;
    }

    /**
     * @param string $operator
     * @param string $expression
     * @return string
     */
    private function unaryOp(string $operator, string $expression): string
    {
        return $operator . $expression;
    }

    /**
     * @param string $operator
     * @param string $left
     * @param string $right
     * @return string
     */
    private function binaryOp(string $operator, string $left, string $right): string
    {
        return '(' . $left . ' ' . $operator . ' ' . $right . ')';
    }

    /**
     * @param string $functionName
     * @param string ...$arguments
     * @return string
     */
    private function functionCall(string $functionName, string ...$arguments): string
    {
        return $functionName . '(' . implode(', ', $arguments) . ')';
    }

    public function add($left, $right): string
    {
        return $this->binaryOp('+', $left, $right);
    }

    public function subtract($left, $right): string
    {
        return $this->binaryOp('-', $left, $right);
    }

    public function multiply($left, $right): string
    {
        return $this->binaryOp('*', $left, $right);
    }

    public function divide($left, $right): string
    {
        return $this->binaryOp('/', $left, $right);
    }

    public function modulo($left, $right): string
    {
        return $this->binaryOp('%', $left, $right);
    }

    public function negate($expression): string
    {
        return $this->unaryOp('-', $expression);
    }

    public function not($expression): string
    {
        return $this->unaryOp('!', $expression);
    }

    public function and($left, $right): string
    {
        return $this->binaryOp('&&', $left, $right);
    }

    public function or($left, $right): string
    {
        return $this->binaryOp('||', $left, $right);
    }

    public function all($expression, ...$expressions): string
    {
        return empty($expressions)
            ? $expression
            : '(' . implode(' && ', array_merge([ $expression ], $expressions )) . ')';
    }

    public function any($expression, ...$expressions): string
    {
        return empty($expressions)
            ? $expression
            : '(' . implode(' || ', array_merge([ $expression ], $expressions )) . ')';
    }

    public function equalTo($left, $right): string
    {
        return $this->binaryOp('=', $left, $right);
    }

    public function greaterThan($left, $right): string
    {
        return $this->binaryOp('>', $left, $right);
    }

    public function greaterThanOrEqualTo($left, $right): string
    {
        return $this->binaryOp('>=', $left, $right);
    }

    public function lesserThan($left, $right): string
    {
        return $this->binaryOp('<', $left, $right);
    }

    public function lesserThanOrEqualTo($left, $right): string
    {
        return $this->binaryOp('<=', $left, $right);
    }

    public function ifThenElse($condition, $true, $false): string
    {
        return (string) $this->getConnection()->getCheckSql($condition, $true, $false);
    }

    public function caseOf($expression, array $branches, $default): string
    {
        return empty($branches)
            ? $default
            : (string) $this->getConnection()->getCaseSql($expression, $branches, $default);
    }

    public function abs($expression): string
    {
        return $this->functionCall('ABS', $expression);
    }

    public function ceil($expression): string
    {
        return $this->functionCall('CEIL', $expression);
    }

    public function floor($expression): string
    {
        return $this->functionCall('FLOOR', $expression);
    }

    public function round($expression, int $digits): string
    {
        return $this->functionCall('ROUND', $expression, (string) $digits);
    }

    public function min($expression, ...$expressions): string
    {
        return empty($expressions)
            ? $expression
            : (string) $this->getConnection()->getLeastSql(array_merge([ $expression ], $expressions));
    }

    public function max($expression, ...$expressions): string
    {
        return empty($expressions)
            ? $expression
            : (string) $this->getConnection()->getGreatestSql(array_merge([ $expression ], $expressions));
    }

    public function pow($base, $exponent): string
    {
        return $this->functionCall('POW', $base, $exponent);
    }

    public function sqrt($expression): string
    {
        return $this->functionCall('SQRT', $expression);
    }

    public function exp($expression): string
    {
        return $this->functionCall('EXP', $expression);
    }

    public function ln($expression): string
    {
        return $this->functionCall('LN', $expression);
    }

    public function log10($expression): string
    {
        return $this->functionCall('LOG', $this->integer(10), $expression);
    }

    public function logn($base, $expression): string
    {
        return $this->functionCall('LOG', $base, $expression);
    }

    public function attributeValue(string $attributeCode): string
    {
        return ($this->attributeCallback)($attributeCode);
    }
}
