<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem;

use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;


/**
 * Resolves name of a temporary table for indexation
 */
class TemporaryResolver implements IndexScopeResolverInterfac
{
    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @inheritDoc
     */
    public function __construct(IndexScopeResolver $indexScopeResolver)
    {
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions)
    {
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);
        $tableName .= \Magento\Framework\Indexer\Table\StrategyInterface::TMP_SUFFIX;

        return $tableName;
    }
}
