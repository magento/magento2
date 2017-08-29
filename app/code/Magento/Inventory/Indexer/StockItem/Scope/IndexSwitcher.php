<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem\Scope;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Provides a functionality to replace main index with its temporary representation
 *
 * @todo refactoring it copy from catalog search module
 */
class IndexSwitcher implements IndexSwitcherInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

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
        $this->resourceConnection = $resource;
        $this->resolver = $indexScopeResolver;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     * @throws IndexTableNotExistException
     */
    public function switchOnTemporaryIndex(array $dimensions, $index)
    {
        if (State::USE_TEMPORARY_INDEX === $this->state->getState()) {
            $temporalIndexTable = $this->resolver->resolve($index, $dimensions);
            if (!$this->resourceConnection->getConnection()->isTableExists($temporalIndexTable)) {
                throw new IndexTableNotExistException(
                    __(
                        "Temporary table for index $index doesn't exist,"
                        . " which is inconsistent with state of scope resolver"
                    )
                );
            }

            $this->state->useRegularIndex();
            $tableName = $this->resolver->resolve($index, $dimensions);
            if ($this->resourceConnection->getConnection()->isTableExists($tableName)) {
                $this->resourceConnection->getConnection()->dropTable($tableName);
            }

            $this->resourceConnection->getConnection()->renameTable($temporalIndexTable, $tableName);
            $this->state->useTemporaryIndex();
        }
    }
}
