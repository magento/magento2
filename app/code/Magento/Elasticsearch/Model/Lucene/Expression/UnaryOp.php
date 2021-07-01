<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

class UnaryOp extends AbstractExpression
{
    const OPERATOR_BITWISE_NOT = '~';
    const OPERATOR_MINUS = '-';
    const OPERATOR_NOT = '!';
    const OPERATOR_NOT_NOT = '!!';

    const OPERATORS = [
        self::OPERATOR_BITWISE_NOT,
        self::OPERATOR_MINUS,
        self::OPERATOR_NOT,
        self::OPERATOR_NOT_NOT,
    ];

    /**
     * @var string
     */
    private $operator;

    /**
     * @var ExpressionInterface
     */
    private $expression;

    /**
     * @param string $operator
     * @param ExpressionInterface $expression
     * @throws \InvalidArgumentException
     */
    public function __construct(string $operator, ExpressionInterface $expression)
    {
        if (!in_array($operator, static::OPERATORS, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown unary operator: "%s".', $operator));
        }

        $this->operator = $operator;
        $this->expression = $expression;
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
    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function getSubExpressions(): array
    {
        return [ $this->getExpression() ];
    }

    public function __toString(): string
    {
        return $this->getOperator() . (string) $this->getExpression();
    }
}
