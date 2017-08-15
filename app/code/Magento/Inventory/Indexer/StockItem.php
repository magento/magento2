<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\Inventory\Indexer\Scope\IndexSwitcherInterface;
use Magento\Inventory\Indexer\Scope\ScopeProxy;
use Magento\Inventory\Indexer\StockItem\DataProvider;
use Magento\Inventory\Indexer\StockItem\DimensionFactory;
use Magento\Inventory\Indexer\StockItem\IndexHandler;
use Magento\Inventory\Indexer\StockItem\TemporaryIndexHandler;
use Magento\InventoryApi\Api\GetAssignedStocksForSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritdoc
 */
class StockItem implements StockItemIndexerInterface
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexHandler
     */
    private $handler;

    /**
     * @var TemporaryIndexHandler
     */
    private $temporaryHandler;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * StockItem constructor.
     * @param DimensionFactory $dimensionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DimensionFactory $dimensionFactory,
        GetAssignedStocksForSourceInterface $assignedStocksForSource,
        IndexHandler $handler,
        DataProvider $dataProvider,
        IndexSwitcherInterface $indexSwitcher
    ) {
        $this->dimensionFactory = $dimensionFactory;
        $this->handler = $handler;
        $this->dataProvider = $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
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
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return StockItem::INDEXER_ID;
    }
}
