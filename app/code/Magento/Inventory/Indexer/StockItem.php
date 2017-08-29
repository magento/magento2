<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer;

use Magento\Inventory\Indexer\StockItem\Scope\IndexSwitcherInterface;
use Magento\Inventory\Indexer\StockItem\Scope\State;
use Magento\Inventory\Indexer\StockItem\IndexDataProvider;
use Magento\Inventory\Indexer\StockItem\DimensionFactory;
use Magento\Inventory\Indexer\StockItem\GetAssignedStockIds;
use Magento\Inventory\Indexer\StockItem\IndexHandler;

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
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexHandler
     */
    private $indexHandler;

    /**
     * @var IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var State
     */
    private $indexScopeState;

    /**
     * @var IndexSwitcherInterface
     */
    private $indexSwitcher;

    /**
     * @param DimensionFactory $dimensionFactory
     * @param GetAssignedStockIds $getAssignedStockIds
     * @param State $indexScopeState
     * @param IndexHandler $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexSwitcherInterface $indexSwitcher
     */
    public function __construct(
        DimensionFactory $dimensionFactory,
        GetAssignedStockIds $getAssignedStockIds,
        State $indexScopeState,
        IndexHandler $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexSwitcherInterface $indexSwitcher
    ) {
        $this->dimensionFactory = $dimensionFactory;
        $this->indexHandler = $indexHandler;
        $this->indexDataProvider = $indexDataProvider;
        $this->getAssignedStockIds = $getAssignedStockIds;
        $this->indexScopeState = $indexScopeState;
        $this->indexSwitcher = $indexSwitcher;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $stockIds = $this->getAssignedStockIds->execute([]);

        foreach ($stockIds as $stockId) {
            $dimensions = [$this->dimensionFactory->create(['name' => 'stock_', 'value' => $stockId])];
            $this->indexScopeState->useTemporaryIndex();
            $this->indexHandler->cleanIndex($dimensions);
            $this->indexHandler->saveIndex($dimensions, $this->indexDataProvider->getData($stockId));

            $this->indexSwitcher->switchOnTemporaryIndex($dimensions, static::INDEXER_ID);
            $this->indexScopeState->useRegularIndex();
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
            $dimensions = [$this->dimensionFactory->create(['name' => 'stock', 'value' => $stockId])];
            $this->indexHandler->cleanIndex($dimensions);
            $this->indexHandler->saveIndex($dimensions, $this->indexDataProvider->getData($stockId, $sourceIds));
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::INDEXER_ID;
    }
}
