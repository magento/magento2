<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexHandlerInterface;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
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
    public function executeRow($sourceItemId)
    {
        $this->executeList([$sourceItemId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $sourceItemIds)
    {

        /**
         * Select stock_id, sku from inventory_source_item
         * INNER JOIN inventory_source_stock_link ON inventory_source_item.source_id = inventory_source_stock_link.source_id
         * where source_item_id IN (1,2)
         */

        // 1  1 sku1
        // 5  3 sku2
        $sourceItemIds = [1, 5];
        $stockIds = [1 => ['sku1'], 2 => ['sku1', 'sku2'] ];

        foreach($stockIds as $stockId => $skuList)
        {
            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->create();

            $this->indexStructure->cleanUp($mainIndexName, $skuList);
            $this->indexHandler->saveIndex(
                $mainIndexName,
                $this->indexDataProvider->getData($stockId, $skuList)
            );
        }
    }
}
