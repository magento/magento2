<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryMultiDimensionalIndexerApi\Model\Alias;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexHandlerInterface;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameBuilder;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexStructureInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

class SourceItemIndexer
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @var IndexDataBySkuListProvider
     */
    private $indexDataBySkuListProvider;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var SiblingSkuListInStockProvider
     */
    private $siblingSkuListInStockProvider;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexHandlerInterface $indexHandler
     * @param IndexStructureInterface $indexStructure
     * @param IndexDataBySkuListProvider $indexDataBySkuListProvider
     * @param SiblingSkuListInStockProvider $siblingSkuListInStockProvider
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        IndexNameBuilder $indexNameBuilder,
        IndexHandlerInterface $indexHandler,
        IndexStructureInterface $indexStructure,
        IndexDataBySkuListProvider $indexDataBySkuListProvider,
        SiblingSkuListInStockProvider $siblingSkuListInStockProvider,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexHandler = $indexHandler;
        $this->indexDataBySkuListProvider = $indexDataBySkuListProvider;
        $this->indexStructure = $indexStructure;
        $this->siblingSkuListInStockProvider = $siblingSkuListInStockProvider;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param array $sourceItemIds
     */
    public function executeList(array $sourceItemIds)
    {
        $skuListInStockList = $this->siblingSkuListInStockProvider->execute($sourceItemIds);

        foreach ($skuListInStockList as $skuListInStock) {
            $stockId = $skuListInStock->getStockId();

            if ($this->defaultStockProvider->getId() === $stockId) {
                continue;
            }
            $skuList = $skuListInStock->getSkuList();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(InventoryIndexer::INDEXER_ID)
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            if (!$this->indexStructure->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
                $this->indexStructure->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            }

            $indexData = $this->indexDataBySkuListProvider->execute($stockId, $skuList);

            $this->indexHandler->cleanIndex(
                $mainIndexName,
                $indexData,
                ResourceConnection::DEFAULT_CONNECTION
            );

            $this->indexHandler->saveIndex(
                $mainIndexName,
                $indexData,
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
}
