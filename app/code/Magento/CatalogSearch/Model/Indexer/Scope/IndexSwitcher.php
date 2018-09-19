<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\CatalogSearch\Model\Indexer\IndexSwitcherInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Provides a functionality to replace main index with its temporary representation
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
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
     * {@inheritdoc}
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
