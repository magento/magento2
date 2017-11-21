<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\MultiDimensionIndex\Alias;
use Magento\Framework\MultiDimensionIndex\IndexHandlerInterface;
use Magento\Framework\MultiDimensionIndex\IndexNameBuilder;
use Magento\Framework\MultiDimensionIndex\IndexStructureInterface;
use Magento\Inventory\Indexer\IndexDataProvider;
use Magento\Inventory\Indexer\Stock\StockIndexer;

/**
 * Source Item indexer
 * Extension point for indexation
 *
 * @api
 */
class SourceItemIndexer implements ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_source_item';

    /**
     * @var GetSkuListInStock
     */
    private $getSkuListInStock;

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
     * @var StockIndexer
     */
    private $stockIndexer;

    /**
     * $indexStructure is reserved name for construct variable (in index internal mechanism)
     *
     * @param GetSkuListInStock $getSkuListInStockToUpdate
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexNameBuilder $indexNameBuilder
     * @param StockIndexer $stockIndexer
     */
    public function __construct(
        GetSkuListInStock $getSkuListInStockToUpdate,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexNameBuilder $indexNameBuilder,
        StockIndexer $stockIndexer
    ) {
        $this->getSkuListInStock = $getSkuListInStockToUpdate;
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
        $skuListInStockList = $this->getSkuListInStock->execute($sourceItemIds);

        foreach ($skuListInStockList as $skuListInStock) {
            $stockId = $skuListInStock->getStockId();
            $skuList = $skuListInStock->getSkuList();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId('inventory_stock')
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
