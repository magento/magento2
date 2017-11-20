<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Source;

use Magento\Inventory\Indexer\Stock\StockIndexerInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * @inheritdoc
 */
class SourceIndexer implements SourceIndexerInterface
{
    /**
     * @var GetPartialReindexData
     */
    private $getPartialReindexData;

    /**
     * @var StockIndexerInterface
     */
    private $stockIndexer;

    /**
     * @param GetPartialReindexData $getPartialReindexData
     * @param StockIndexerInterface $stockIndexer
     */
    public function __construct(
        GetPartialReindexData $getPartialReindexData,
        StockIndexerInterface $stockIndexer
    ) {
        $this->getPartialReindexData = $getPartialReindexData;
        $this->stockIndexer = $stockIndexer;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->stockIndexer->executeFull();
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
        $stockList = $this->getPartialReindexData->execute($sourceIds);
        $stockIds = array_column($stockList, StockInterface::STOCK_ID);
        $this->stockIndexer->executeList($stockIds);
    }
}
