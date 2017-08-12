<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem;


use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * @inheritdoc
 */
class IndexSwitcher implements IndexSwitcherInterface
{

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeProxy
     */
    private $resolver;

    /**
     * @var State
     */
    private $state;

    /**
     * @var TemporaryResolver
     */
    private $temporaryResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param State $state
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolverInterface $indexScopeResolver,
        IndexScopeResolverInterface $temporaryResolver,
        State $state
    ) {
        $this->resource = $resource;
        $this->resolver = $indexScopeResolver;
        $this->temporaryResolver = $temporaryResolver;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function switchIndex(array $dimensions)
    {
        $index = StockItemIndexerInterface::INDEXER_ID;

        $temporalIndexTable = $this->temporaryResolver->resolve($index, $dimensions);
        if (!$this->resource->getConnection()->isTableExists($temporalIndexTable)) {
            throw new LocalizedException(
                __(
                    "Temporary table for index $index doesn't exist,"
                    . " which is inconsistent with state of scope resolver"
                )
            );
        }

        $tableName = $this->resolver->resolve($index, $dimensions);
        if ($this->resource->getConnection()->isTableExists($tableName)) {
            $this->resource->getConnection()->dropTable($tableName);
        }

        $this->resource->getConnection()->renameTable($temporalIndexTable, $tableName);
    }
}
