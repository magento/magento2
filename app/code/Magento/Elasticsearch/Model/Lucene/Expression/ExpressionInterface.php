<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

interface ExpressionInterface
{
    /**
     * @return ExpressionInterface[]
     */
    public function getSubExpressions(): array;

    /**
     * @return string[]
     */
    public function getParameterNames(): array;

    /**
     * @return string
     */
    public function __toString(): string;
}
