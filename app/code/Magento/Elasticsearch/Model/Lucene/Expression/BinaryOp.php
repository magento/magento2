<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

class BinaryOp extends AbstractExpression
{
    const OPERATOR_BITWISE_AND = '&';
    const OPERATOR_BITWISE_OR = '|';
    const OPERATOR_BITWISE_XOR = '^';
    const OPERATOR_DIVIDE = '/';
    const OPERATOR_EQ = '==';
    const OPERATOR_GT = '>';
    const OPERATOR_GT_EQ = '>=';
    const OPERATOR_LOGICAL_AND = '&&';
    const OPERATOR_LOGICAL_OR = '||';
    const OPERATOR_LT = '<';
    const OPERATOR_LT_EQ = '<=';
    const OPERATOR_MINUS = '-';
    const OPERATOR_MOD = '%';
    const OPERATOR_TIMES = '*';
    const OPERATOR_PLUS = '+';
    const OPERATOR_SHIFT_LEFT = '<<';
    const OPERATOR_SHIFT_RIGHT = '>>';
    const OPERATOR_SHIFT_RIGHT_UNSIGNED = '>>>';

    const OPERATORS = [
        self::OPERATOR_BITWISE_AND,
        self::OPERATOR_BITWISE_OR,
        self::OPERATOR_BITWISE_XOR,
        self::OPERATOR_DIVIDE,
        self::OPERATOR_EQ,
        self::OPERATOR_GT,
        self::OPERATOR_GT_EQ,
        self::OPERATOR_LOGICAL_AND,
        self::OPERATOR_LOGICAL_OR,
        self::OPERATOR_LT,
        self::OPERATOR_LT_EQ,
        self::OPERATOR_MINUS,
        self::OPERATOR_MOD,
        self::OPERATOR_TIMES,
        self::OPERATOR_PLUS,
        self::OPERATOR_SHIFT_LEFT,
        self::OPERATOR_SHIFT_RIGHT,
        self::OPERATOR_SHIFT_RIGHT_UNSIGNED,
    ];

    /**
     * @var string
     */
    private $operator;

    /**
     * @var ExpressionInterface
     */
    private $left;

    /**
     * @var ExpressionInterface
     */
    private $right;

    /**
     * @param string $operator
     * @param ExpressionInterface $left
     * @param ExpressionInterface $right
     * @throws \InvalidArgumentException
     */
    public function __construct(string $operator, ExpressionInterface $left, ExpressionInterface $right)
    {
        if (!in_array($operator, static::OPERATORS, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown binary operator: "%s".', $operator));
        }

        $this->operator = $operator;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return ExpressionInterface
     */
    public function getLeft(): ExpressionInterface
    {
        return $this->left;
    }

    /**
     * @return ExpressionInterface
     */
    public function getRight(): ExpressionInterface
    {
        return $this->right;
    }

    public function getSubExpressions(): array
    {
        return [ $this->getLeft(), $this->getRight() ];
    }

    public function __toString(): string
    {
        return '('
            . (string) $this->getLeft()
            . ' '
            . $this->getOperator()
            . ' '
            . (string) $this->getRight()
            . ')';
    }
}
