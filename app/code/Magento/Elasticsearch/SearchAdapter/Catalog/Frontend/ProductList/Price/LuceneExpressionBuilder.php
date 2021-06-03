<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Catalog\Frontend\ProductList\Price;

use Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price\ExpressionBuilderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Lucene\Expression\BinaryOp;
use Magento\Elasticsearch\Model\Lucene\Expression\Field;
use Magento\Elasticsearch\Model\Lucene\Expression\Constant;
use Magento\Elasticsearch\Model\Lucene\Expression\ExpressionInterface;
use Magento\Elasticsearch\Model\Lucene\Expression\FunctionCall;
use Magento\Elasticsearch\Model\Lucene\Expression\TernaryOp;
use Magento\Elasticsearch\Model\Lucene\Expression\UnaryOp;

class LuceneExpressionBuilder implements ExpressionBuilderInterface
{
    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param AttributeProvider $attributeAdapterProvider
     */
    public function __construct(FieldMapperInterface $fieldMapper, AttributeProvider $attributeAdapterProvider)
    {
        $this->fieldMapper = $fieldMapper;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
    }

    public function bool(bool $value): ExpressionInterface
    {
        return new Constant($value ? 1 : 0);
    }

    public function integer(int $value): ExpressionInterface
    {
        return new Constant($value);
    }

    public function double(float $value): ExpressionInterface
    {
        return new Constant($value);
    }

    public function add($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_PLUS, $left, $right);
    }

    public function subtract($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_MINUS, $left, $right);
    }

    public function multiply($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_TIMES, $left, $right);
    }

    public function divide($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_DIVIDE, $left, $right);
    }

    public function modulo($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_MOD, $left, $right);
    }

    public function negate($expression): ExpressionInterface
    {
        return $this->multiply($expression, $this->integer(-1));
    }

    public function not($expression): ExpressionInterface
    {
        return new UnaryOp(UnaryOp::OPERATOR_NOT, $expression);
    }

    public function and($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_LOGICAL_AND, $left, $right);
    }

    public function or($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_LOGICAL_OR, $left, $right);
    }

    public function all($expression, ...$expressions): ExpressionInterface
    {
        foreach ($expressions as $subExpression) {
            $expression = $this->and($expression, $subExpression);
        }

        return $expression;
    }

    public function any($expression, ...$expressions): ExpressionInterface
    {
        foreach ($expressions as $subExpression) {
            $expression = $this->or($expression, $subExpression);
        }

        return $expression;
    }

    public function equalTo($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_EQ, $left, $right);
    }

    public function greaterThan($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_GT, $left, $right);
    }

    public function greaterThanOrEqualTo($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_GT_EQ, $left, $right);
    }

    public function lesserThan($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_LT, $left, $right);
    }

    public function lesserThanOrEqualTo($left, $right): ExpressionInterface
    {
        return new BinaryOp(BinaryOp::OPERATOR_LT_EQ, $left, $right);
    }

    public function ifThenElse($condition, $true, $false): ExpressionInterface
    {
        return new TernaryOp($condition, $true, $false);
    }

    public function caseOf($baseExpression, array $branches, $default): ExpressionInterface
    {
        if (empty($branches)) {
            return $default;
        }

        $conditions = [];
        $results = [];

        /** @var ExpressionInterface $branchResult */
        foreach ($branches as $value => $branchResult) {
            $branchCondition = $this->equalTo($baseExpression, $this->double((float) $value));

            foreach ($results as $index => $result) {
                if ($branchResult == $result) {
                    $conditions[$index] = $this->or($conditions[$index], $branchCondition);
                    continue 2;
                }
            }

            $conditions[] = $branchCondition;
            $results[] = $branchResult;
        }

        $conditions = array_reverse($conditions);
        $results = array_reverse($results);
        $expression = null;

        foreach ($conditions as $index => $condition) {
            $expression = $this->ifThenElse(
                $condition,
                $results[$index],
                $expression ?? $default,
            );
        }

        return $expression;
    }

    public function abs($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_ABS, $expression);
    }

    public function ceil($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_CEIL, $expression);
    }

    public function floor($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_FLOOR, $expression);
    }

    public function round($expression, int $digits): ExpressionInterface
    {
        $multiplier = $this->double(pow(10, $digits));

        return $this->divide(
            $this->floor(
                $this->add(
                    $this->double(0.5),
                    $this->multiply($expression, $multiplier)
                )
            ),
            $multiplier
        );
    }

    public function min($expression, ...$expressions): ExpressionInterface
    {
        return empty($expressions)
            ? $expression
            : new FunctionCall(FunctionCall::FUNCTION_MIN, $expression, ...$expressions);
    }

    public function max($expression, ...$expressions): ExpressionInterface
    {
        return empty($expressions)
            ? $expression
            : new FunctionCall(FunctionCall::FUNCTION_MAX, $expression, ...$expressions);
    }

    public function pow($base, $exponent): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_POW, $base, $exponent);
    }

    public function sqrt($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_SQRT, $expression);
    }

    public function exp($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_EXP, $expression);
    }

    public function ln($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_LN, $expression);
    }

    public function log10($expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_LOG10, $expression);
    }

    public function logn($base, $expression): ExpressionInterface
    {
        return new FunctionCall(FunctionCall::FUNCTION_LOGN, $base, $expression);
    }

    public function attributeValue(string $attributeCode): ExpressionInterface
    {
        $attribute = $this->attributeAdapterProvider->getByAttributeCode($attributeCode);

        if (!$attribute->isIntegerType() || !$attribute->isFloatType()) {
            throw new \InvalidArgumentException(
                sprintf('The attribute "%s" can not be used in price expressions.', $attributeCode)
            );
        }

        return new Field\Property(
            $this->fieldMapper->getFieldName(
                $attributeCode,
                [ 'type' => FieldMapperInterface::TYPE_FILTER ]
            ),
            [ 'value' ]
        );
    }
}
