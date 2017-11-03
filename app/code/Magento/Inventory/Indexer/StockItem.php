<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\StockItem\GetFullReindexData;
use Magento\Inventory\Indexer\StockItem\IndexDataProvider;

/**
 * @inheritdoc
 */
class StockItem implements StockItemIndexerInterface
{
    /**
     * @var GetFullReindexData
     */
    private $getFullReindexData;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructureHandler;

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
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexNameBuilder $indexNameBuilder,
        IndexDataProvider $indexDataProvider,
        IndexTableSwitcherInterface $indexTableSwitcher
    ) {
        $this->getFullReindexData = $getFullReindexData;
        $this->indexStructureHandler = $indexStructureHandler;
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

        foreach ($stockIds as $stockId) {
            $replicaIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_REPLICA)
                ->build();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexStructureHandler->delete($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructureHandler->create($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);

            if (!$this->indexStructureHandler->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
                $this->indexStructureHandler->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            }

            $this->indexHandler->saveIndex(
                $replicaIndexName,
                $this->indexDataProvider->getData($stockId),
                ResourceConnection::DEFAULT_CONNECTION
            );
            $this->indexTableSwitcher->switch($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructureHandler->delete($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeRow($stockItemId)
    {
        $this->executeList([$stockItemId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $stockIds)
    {
        /** @var int $stockId */
        foreach ($stockIds as $stockId) {
            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexStructureHandler->delete($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructureHandler->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexHandler->saveIndex(
                $mainIndexName,
                $this->indexDataProvider->getData($stockId),
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
}
