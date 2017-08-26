<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\Inventory\Indexer\Scope\IndexSwitcherInterface;
use Magento\Inventory\Indexer\Scope\State;
use Magento\Inventory\Indexer\StockItem\DataProvider;
use Magento\Inventory\Indexer\StockItem\DimensionFactory;
use Magento\Inventory\Indexer\StockItem\IndexHandler;
use Magento\Inventory\Indexer\StockItem\Service\GetAssignedStocksInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritdoc
 */
class StockItem implements StockItemIndexerInterface
{

    /**
     * @var GetAssignedStocksInterface
     */
    private $assignedStocksForSource;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexHandler
     */
    private $handler;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var State
     */
    private $indexScopeState;

    /**
     * @var IndexSwitcherInterface
     */
    private $indexSwitcher;

    /**
     * StockItem constructor.
     * @param DimensionFactory $dimensionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DimensionFactory $dimensionFactory,
        GetAssignedStocksInterface $assignedStocksForSource,
        State $indexScopeState,
        IndexHandler $handler,
        DataProvider $dataProvider,
        IndexSwitcherInterface $indexSwitcher
    ) {
        $this->dimensionFactory = $dimensionFactory;
        $this->handler = $handler;
        $this->dataProvider = $dataProvider;
        $this->assignedStocksForSource = $assignedStocksForSource;
        $this->indexScopeState = $indexScopeState;
        $this->indexSwitcher = $indexSwitcher;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $stocks = $this->assignedStocksForSource->execute([]);

        foreach ($stocks as $stockId) {
            $dimension = [$this->dimensionFactory->create(['name' => 'stock_', 'value' => $stockId])];
            $this->indexScopeState->useTemporaryIndex();
            $this->handler->cleanIndex($dimension);
            $this->handler->saveIndex($dimension, $this->dataProvider->fetchDocuments($stockId, []));

            $this->indexSwitcher->switchIndex($dimension, static::INDEXER_ID);
            $this->indexScopeState->useRegularIndex();
        }
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        $stockIds = $this->assignedStocksForSource->execute($ids);

        foreach ($stockIds as $stockId) {
            $dimension = [$this->dimensionFactory->create(['name' => 'stock', 'value' => $stockId])];
            $this->handler->cleanIndex($dimension);
            $this->handler->saveIndex($dimension, $this->dataProvider->fetchDocuments($stockId, $ids));
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return StockItem::INDEXER_ID;
    }
}
