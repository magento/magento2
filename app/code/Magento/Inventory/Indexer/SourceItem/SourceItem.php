<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexDataProvider;
use Magento\Inventory\Indexer\IndexHandlerInterface;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\Stock\StockIndexerInterface;

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
     * @var StockIndexerInterface
     */
    private $stockIndexer;

    /**
     * @param GetPartialReindexData $getPartialReindexData
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexNameBuilder $indexNameBuilder
     * @param StockIndexerInterface $stockIndexer
     */
    public function __construct(
        GetPartialReindexData $getPartialReindexData,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexNameBuilder $indexNameBuilder,
        StockIndexerInterface $stockIndexer
    ) {
        $this->getPartialReindexData = $getPartialReindexData;
        $this->indexStructure = $indexStructureHandler;
        $this->indexHandler = $indexHandler;
        $this->indexDataProvider = $indexDataProvider;
        $this->indexNameBuilder = $indexNameBuilder;
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
    public function executeList(array $sourceItemIds)
    {
        $skuListInStockToUpdateList = $this->getPartialReindexData->execute($sourceItemIds);

        foreach ($skuListInStockToUpdateList as $skuListInStockToUpdate) {
            $stockId = $skuListInStockToUpdate->getStockId();
            $skuList = $skuListInStockToUpdate->getSkuList();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(self::INDEXER_ID)
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            if (!$this->indexStructure->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
                $this->indexStructure->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
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
