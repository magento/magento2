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
 * @since 2.2.0
 */
class IndexSwitcher implements IndexSwitcherInterface
{
    /**
     * @var Resource
     * @since 2.2.0
     */
    private $resource;

    /**
     * @var ScopeProxy
     * @since 2.2.0
     */
    private $resolver;

    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param State $state
     * @since 2.2.0
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
     * @since 2.2.0
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
