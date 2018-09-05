<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;

/**
 * Resolves name of a temporary table for indexation
 *
 * @deprecated
 * @see ElasticSearch module is default search engine starting from 2.3. CatalogSearch would be removed in 2.4
 */
class TemporaryResolver implements \Magento\Framework\Search\Request\IndexScopeResolverInterface
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
