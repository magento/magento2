<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexDataProvider;
use Magento\Inventory\Indexer\IndexHandlerInterface;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\IndexTableSwitcherInterface;

/**
 * @inheritdoc
 */
class StockIndexer implements StockIndexerInterface
{
    /**
     * @var GetFullReindexData
     */
    private $getFullReindexData;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var IndexTableSwitcherInterface
     */
    private $indexTableSwitcher;

    public function __construct(
        GetFullReindexData $getFullReindexData,
        IndexStructureInterface $indexStructure,
        IndexHandlerInterface $indexHandler,
        IndexNameBuilder $indexNameBuilder,
        IndexDataProvider $indexDataProvider,
        IndexTableSwitcherInterface $indexTableSwitcher
    ) {
        $this->getFullReindexData = $getFullReindexData;
        $this->indexStructure = $indexStructure;
        $this->indexHandler = $indexHandler;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexDataProvider = $indexDataProvider;
        $this->indexTableSwitcher = $indexTableSwitcher;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $stockIds = $this->getFullReindexData->execute();
        $this->executeList($stockIds);
    }

    /**
     * @inheritdoc
     */
    public function executeRow($stockId)
    {
        $this->executeList([$stockId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $stockIds)
    {
        foreach ($stockIds as $stockId) {
            $replicaIndexName = $this->indexNameBuilder
                ->setIndexId(self::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_REPLICA)
                ->build();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(self::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexStructure->delete($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructure->create($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);

            if (!$this->indexStructure->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
                $this->indexStructure->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            }

            $this->indexHandler->saveIndex(
                $replicaIndexName,
                $this->indexDataProvider->getData($stockId),
                ResourceConnection::DEFAULT_CONNECTION
            );
            $this->indexTableSwitcher->switch($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructure->delete($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);
        }
    }
}
