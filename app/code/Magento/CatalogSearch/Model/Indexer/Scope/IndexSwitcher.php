<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Provides a functionality to replace main index table with its temporary state
 */
class IndexSwitcher
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
     * @param ResourceConnection $resource
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param State $state
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolverInterface $indexScopeResolver,
        State $state
    ) {
        $this->resource = $resource;
        $this->resolver = $indexScopeResolver;
        $this->state = $state;
    }

    /**
     * Switch current index with temporary index
     *
     * It will drop current index table and rename temporary index table to the current index table.
     *
     * @param array $dimensions
     * @return void
     * @throws IndexTableNotExistException
     */
    public function switchIndex(array $dimensions)
    {
        if (State::USE_TEMPORARY_INDEX === $this->state->getState()) {
            $index = \Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID;

            $temporalIndexTable = $this->resolver->resolve($index, $dimensions);
            if (!$this->resource->getConnection()->isTableExists($temporalIndexTable)) {
                throw new IndexTableNotExistException(
                    __(
                        "Temporary table for index $index doesn't exist,"
                        . " which is inconsistent with state of scope resolver"
                    )
                );
            }

            $this->state->useRegularIndex();
            $tableName = $this->resolver->resolve($index, $dimensions);
            if ($this->resource->getConnection()->isTableExists($tableName)) {
                $this->resource->getConnection()->dropTable($tableName);
            }

            $this->resource->getConnection()->renameTable($temporalIndexTable, $tableName);
            $this->state->useTemporaryIndex();
        }
    }
}
