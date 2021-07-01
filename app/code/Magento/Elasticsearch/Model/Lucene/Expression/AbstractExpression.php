<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

abstract class AbstractExpression implements ExpressionInterface
{
    public function getSubExpressions(): array
    {
        return [];
    }

    public function getParameterNames(): array
    {
        $parameterNames = [];

        foreach ($this->getSubExpressions() as $expression) {
            $parameterNames = array_merge($parameterNames, $expression->getParameterNames());
        }

        return array_unique($parameterNames);
    }
}
