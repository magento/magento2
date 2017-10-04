<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexHandlerInterface;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\IndexTableSwitcherInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\Inventory\Indexer\StockItem\GetPartialReindexData;
use Magento\Inventory\Indexer\StockItem\GetFullReindexData;
use Magento\Inventory\Indexer\StockItem\IndexDataProvider;

/**
 * @inheritdoc
 */
class StockItem implements StockItemIndexerInterface
{
    /**
     * @var GetPartialReindexData
     */
    private $getPartialReindexData;

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
     * @var IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var IndexTableSwitcherInterface
     */
    private $indexTableSwitcher;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @param GetPartialReindexData $getPartialReindexData
     * @param GetFullReindexData $getFullReindexData
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexTableSwitcherInterface $indexTableSwitcher
     * @param IndexNameBuilder $indexNameBuilder
     */
    public function __construct(
        GetPartialReindexData $getPartialReindexData,
        GetFullReindexData $getFullReindexData,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexTableSwitcherInterface $indexTableSwitcher,
        IndexNameBuilder $indexNameBuilder
    ) {
        $this->getPartialReindexData = $getPartialReindexData;
        $this->getFullReindexData = $getFullReindexData;
        $this->indexStructure = $indexStructureHandler;
        $this->indexHandler = $indexHandler;
        $this->indexDataProvider = $indexDataProvider;
        $this->indexTableSwitcher = $indexTableSwitcher;
        $this->indexNameBuilder = $indexNameBuilder;
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

    /**
     * @inheritdoc
     */
    public function executeRow($sourceItemId)
    {
        $this->executeList([$sourceItemId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $sourceItemIds)
    {
        $skuListInStockToUpdateList = $this->getPartialReindexData->execute($sourceItemIds);

        foreach ($skuListInStockToUpdateList as $skuListInStockToUpdate) {
            $stockId = $skuListInStockToUpdate->getStockId();
            $skuList = $skuListInStockToUpdate->getSkuList();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexHandler->cleanIndex(
                $mainIndexName,
                new \ArrayIterator($skuList),
                ResourceConnection::DEFAULT_CONNECTION
            );
            $this->indexHandler->saveIndex(
                $mainIndexName,
                $this->indexDataProvider->getData($stockId, $skuList),
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
}
