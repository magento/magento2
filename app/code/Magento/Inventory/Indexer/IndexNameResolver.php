<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer;

use Magento\Framework\Search\Request\IndexScopeResolverInterface;

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
     * @var IndexTableSwitcherInterface
     */
    private $indexTableSwitcher;

    /**
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param IndexTableSwitcherInterface $indexTableSwitcher
     */
    public function __construct(
        IndexScopeResolverInterface $indexScopeResolver,
        IndexTableSwitcherInterface $indexTableSwitcher
    ) {
        $this->indexScopeResolver = $indexScopeResolver;
        $this->indexTableSwitcher = $indexTableSwitcher;
    }

    /**
     * @inheritdoc
     */
    public function resolveName(IndexName $indexName): string
    {
        $tableName = $this->indexScopeResolver->resolve($indexName->getIndexId(), $indexName->getDimensions());

        if ($indexName->getAlias()->getValue() === Alias::ALIAS_REPLICA) {
            $tableName = $this->indexTableSwitcher->getAdditionalTableName($tableName);
        }
        return $tableName;
    }
}
