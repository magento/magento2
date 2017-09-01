<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexHandlerInterface;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexTableSwitcherInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * @inheritdoc
 */
class StockItem implements StockItemIndexerInterface
{
    /**
     * @var GetAssignedStockIds
     */
    private $getAssignedStockIds;

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
     * @param GetAssignedStockIds $getAssignedStockIds
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexTableSwitcherInterface $indexTableSwitcher
     * @param IndexNameBuilder $indexNameBuilder
     *
     * $indexStructureHandler name is for avoiding conflict with legacy index implementation
     * @see \Magento\Indexer\Model\Indexer::getActionInstance
     */
    public function __construct(
        GetAssignedStockIds $getAssignedStockIds,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexTableSwitcherInterface $indexTableSwitcher,
        IndexNameBuilder $indexNameBuilder
    ) {
        $this->getAssignedStockIds = $getAssignedStockIds;
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
        $stockIds = $this->getAssignedStockIds->execute([]);

        foreach ($stockIds as $stockId) {
            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->create();
            $this->indexStructure->create($mainIndexName);

            $replicaIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_REPLICA)
                ->create();
            $this->indexStructure->create($replicaIndexName);

            $this->indexHandler->saveIndex($replicaIndexName, $this->indexDataProvider->getData($stockId));
            $this->indexTableSwitcher->switch($mainIndexName);
            $this->indexStructure->delete($replicaIndexName);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeRow($sourceId)
    {
        $this->executeList([$sourceId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $sourceIds)
    {
        $stockIds = $this->getAssignedStockIds->execute($sourceIds);

        foreach ($stockIds as $stockId) {
            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->create();
            // TODO: we do not need to clear index
            $this->indexStructure->create($mainIndexName);
            $this->indexHandler->saveIndex($mainIndexName, $this->indexDataProvider->getData($stockId));
        }
    }
}
