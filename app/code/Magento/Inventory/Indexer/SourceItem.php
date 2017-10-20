<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\StockItem\GetPartialReindexData;
use Magento\Inventory\Indexer\StockItem\IndexDataProvider;

/**
 * @inheritdoc
 */
class SourceItem implements SourceItemIndexerInterface
{
    /**
     * @var GetPartialReindexData
     */
    private $getPartialReindexData;

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
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var StockItemIndexerInterface
     */
    private $stockItemIndexer;

    /**
     * @param GetPartialReindexData $getPartialReindexData
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexNameBuilder $indexNameBuilder
     * @param StockItemIndexerInterface $stockItemIndexer
     */
    public function __construct(
        GetPartialReindexData $getPartialReindexData,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexNameBuilder $indexNameBuilder,
        StockItemIndexerInterface $stockItemIndexer
    ) {
        $this->getPartialReindexData = $getPartialReindexData;
        $this->indexStructure = $indexStructureHandler;
        $this->indexHandler = $indexHandler;
        $this->indexDataProvider = $indexDataProvider;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->stockItemIndexer = $stockItemIndexer;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->stockItemIndexer->executeFull();
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

            if (!$this->indexStructure->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
                return;
            }

            $this->indexHandler->cleanIndex(
                $mainIndexName,
                new \ArrayIterator($skuList),
                ResourceConnection::DEFAULT_CONNECTION
            );

            $this->indexHandler->saveIndex(
                $mainIndexName,
                $this->indexDataProvider->getDataBySkuList($stockId, $skuList),
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
}
