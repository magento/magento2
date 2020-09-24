<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Model\Indexer\Design;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\Grid;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

class IndexerHandler extends Grid
{
    /**
     * @var FlatScopeResolver
     */
    private $flatScopeResolver;

    /**
     * @param IndexStructureInterface $indexStructure
     * @param ResourceConnection $resource
     * @param Batch $batch
     * @param IndexScopeResolver $indexScopeResolver
     * @param FlatScopeResolver $flatScopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ResourceConnection $resource,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver,
        array $data,
        $batchSize = 100
    ) {
        parent::__construct(
            $indexStructure,
            $resource,
            $batch,
            $indexScopeResolver,
            $flatScopeResolver,
            $data,
            $batchSize
        );

        $this->flatScopeResolver = $flatScopeResolver;
    }

    /**
     * Clean index table by deleting all records unconditionally or create the index table if not exists
     *
     * @param $dimensions
     * @return IndexerHandler
     */
    public function cleanIndex($dimensions)
    {
        $tableName = $this->flatScopeResolver->resolve($this->getIndexName(), $dimensions);

        if ($this->connection->isTableExists($tableName)) {
            $this->connection->delete($tableName);
        } else {
            $this->indexStructure->create($this->getIndexName(), $this->fields, $dimensions);
        }

        return $this;
    }
}
