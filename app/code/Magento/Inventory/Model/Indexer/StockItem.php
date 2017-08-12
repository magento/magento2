<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Inventory\Model\Indexer\StockItem\DataProvider;
use Magento\Inventory\Model\Indexer\StockItem\DimensionFactory;
use Magento\Inventory\Model\Indexer\StockItem\IndexHandler;
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
        StoreManagerInterface $storeManager,
        IndexHandler $handler,
        DataProvider $dataProvider
    ) {
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->handler = $handler;
        $this->dataProvider = $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $dimension = [$this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId])];
            $this->handler->setIndexName($this->getTemporaryIndexName());
            $this->handler->cleanIndex($dimension);
            $this->handler->saveIndex($dimension, $this->dataProvider->fetchDocuments([]));
            // TODO Implement switch
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
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $dimension = [$this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId])];
            $this->handler->setIndexName($this->getIndexName());
            $this->handler->deleteIndex($dimension, new \ArrayObject($ids));
            $this->handler->saveIndex($dimension, $this->dataProvider->fetchDocuments($ids));
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return StockItem::INDEXER_ID;
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return StockItem::INDEXER_ID.StrategyInterface::IDX_SUFFIX;
    }

    /**
     * @return string
     */
    private function getTemporaryIndexName()
    {
        return StockItem::INDEXER_ID. StrategyInterface::TMP_SUFFIX;
    }
}
