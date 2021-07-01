<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

class TernaryOp extends AbstractExpression
{
    /**
     * @var ExpressionInterface
     */
    private $condition;

    /**
     * @var ExpressionInterface
     */
    private $true;

    /**
     * @var ExpressionInterface
     */
    private $false;

    /**
     * @param ExpressionInterface $condition
     * @param ExpressionInterface $true
     * @param ExpressionInterface $false
     */
    public function __construct(ExpressionInterface $condition, ExpressionInterface $true, ExpressionInterface $false)
    {
        $this->condition = $condition;
        $this->true = $true;
        $this->false = $false;
    }

    /**
     * @return ExpressionInterface
     */
    public function getCondition(): ExpressionInterface
    {
        return $this->condition;
    }

    /**
     * @return ExpressionInterface
     */
    public function getTrue(): ExpressionInterface
    {
        return $this->true;
    }

    /**
     * @return ExpressionInterface
     */
    public function getFalse(): ExpressionInterface
    {
        return $this->false;
    }

    public function getSubExpressions(): array
    {
        return [ $this->getCondition(), $this->getTrue(), $this->getFalse() ];
    }

    public function __toString(): string
    {
        return '('
            . (string) $this->getCondition()
            . ' ? '
            . (string) $this->getTrue()
            . ' : '
            . (string) $this->getFalse()
            . ')';
    }
}
