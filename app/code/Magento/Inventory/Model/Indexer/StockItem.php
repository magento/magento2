<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface as SaveHandlerIndexerInterface;
use Magento\Inventory\Model\Indexer\StockItem\DimensionFactory;
use Magento\Inventory\Model\Indexer\StockItem\IndexHandlerFactory;

/**
 * @inheritdoc
 */
class StockItem implements StockItemIndexerInterface
{

    /**
     * @var HandlerIndexerInterfaceFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var array
     */
    private $data;


    public function __construct(
        IndexHandlerFactory $indexerHandler,
        DimensionFactory $dimensionFactory,
        array $data
    ) {
        $this->indexerHandlerFactory = $indexerHandler;
        $this->dimensionFactory = $dimensionFactory;
        $this->data = $data;
    }


    /**
     * @inheritdoc
     */
    public function executeFull()
    {

        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data,
            'indexScopeResolver' => \Magento\Inventory\Model\Indexer\StockItem\TemporaryResolver::class
        ]);
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
        /** @var SaveHandlerIndexerInterface $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data,
            'indexScopeResolver' => '\Magento\Inventory\Model\Indexer\StockItem\TemporaryResolver'
        ]);
        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
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
