<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Model\Indexer\Design;

use Magento\Framework\App\ResourceConnection;
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

    public function __construct(
        IndexStructureInterface $indexStructure,
        ResourceConnection $resource,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver,
        array $data,
        $batchSize = 100)
    {
        parent::__construct(
            $indexStructure,
            $resource,
            $batch,
            $indexScopeResolver,
            $flatScopeResolver,
            $data,
            $batchSize);

        $this->flatScopeResolver = $flatScopeResolver;
    }

    /**
     * @return bool
     */
    private function isFlatTableExists()
    {
        $adapter = $this->resource->getConnection('write');
        $tableName = $this->flatScopeResolver->resolve($this->getIndexName(), []);

        return $adapter->isTableExists($tableName);
    }

    /**
     * Clean index table by deleting all records
     *
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        if ($this->isFlatTableExists()) {
            $adapter = $this->resource->getConnection('write');
            $tableName = $this->flatScopeResolver->resolve($this->getIndexName(), $dimensions);
            $adapter->delete($tableName);
        } else {
            $this->indexStructure->create($this->getIndexName(), $this->fields, $dimensions);
        }
    }
}
