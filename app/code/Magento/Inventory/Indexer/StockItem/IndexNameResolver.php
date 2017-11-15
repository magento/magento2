<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Inventory\Indexer\ActiveTableSwitcher;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexName;
use Magento\Inventory\Indexer\IndexNameResolverInterface;

/**
 * @inheritdoc
 */
class IndexNameResolver implements IndexNameResolverInterface
{
    /**
     * @var IndexScopeResolverInterface
     */
    private $indexScopeResolver;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param ActiveTableSwitcher $activeTableSwitcher
     */
    public function __construct(
        IndexScopeResolverInterface $indexScopeResolver,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->indexScopeResolver = $indexScopeResolver;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    /**
     * @inheritdoc
     */
    public function resolveName(IndexName $indexName): string
    {
        $tableName = $this->indexScopeResolver->resolve($indexName->getIndexId(), $indexName->getDimensions());

        if ($indexName->getAlias()->getValue() === Alias::ALIAS_REPLICA) {
            $tableName = $this->activeTableSwitcher->getAdditionalTableName($tableName);
        }
        return $tableName;
    }
}
