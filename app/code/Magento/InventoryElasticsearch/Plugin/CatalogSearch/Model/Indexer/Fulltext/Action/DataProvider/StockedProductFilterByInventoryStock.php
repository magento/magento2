<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryElasticsearch\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Filter products by stock status.
 */
class StockedProductFilterByInventoryStock
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $stockStatusCriteriaFactory;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param Config $config
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        Config $config,
        StockConfigurationInterface $stockConfiguration,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        StockStatusRepositoryInterface $stockStatusRepository,
        DefaultStockProviderInterface $defaultStockProvider,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->config = $config;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Filter out stock options for configurable product.
     *
     * @param DataProvider $dataProvider
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareProductIndex(
        DataProvider $dataProvider,
        $indexData,
        $productData,
        $storeId
    ) {
        if ($this->config->isElasticsearchEnabled() && !$this->stockConfiguration->isShowOutOfStock($storeId)) {
            $productIds = array_keys($indexData);
            $store = $this->storeRepository->getById($storeId);
            $stock = $this->stockByWebsiteIdResolver->execute((int)$store->getWebsiteId());
            $stockId = $stock->getStockId();

            if ($this->defaultStockProvider->getId() === $stockId) {
                $stockStatuses = $this->getStockStatusesFromDefaultStock($productIds);
            } else {
                $stockStatuses = $this->getStockStatusesFromCustomStock($productIds, $stockId);
            }

            $indexData = array_intersect_key($indexData, $stockStatuses);
        }

        return [
            $indexData,
            $productData,
            $storeId,
        ];
    }

    /**
     * Get product stock statuses on default stock.
     *
     * @param array $productIds
     * @return array
     */
    private function getStockStatusesFromDefaultStock(array $productIds): array
    {
        $stockStatusCriteria = $this->stockStatusCriteriaFactory->create();
        $stockStatusCriteria->setProductsFilter($productIds);
        $stockStatusCollection = $this->stockStatusRepository->getList($stockStatusCriteria);
        $stockStatuses = $stockStatusCollection->getItems();

        return array_filter($stockStatuses, function (StockStatusInterface $stockStatus) {
            return StockStatusInterface::STATUS_IN_STOCK === (int)$stockStatus->getStockStatus();
        });
    }

    /**
     * Get product stock statuses on custom stock.
     *
     * @param array $productIds
     * @param int $stockId
     * @return array
     */
    private function getStockStatusesFromCustomStock(array $productIds, int $stockId): array
    {
        $stockTable = $this->stockIndexTableNameResolver->execute($stockId);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select();
        $select->from(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['entity_id']
        );
        $select->joinInner(
            ['stock' => $stockTable],
            'product.sku = stock.sku',
            ['is_salable']
        );
        $select->where('product.entity_id IN (?)', $productIds);
        $select->where('stock.is_salable = ?', 1);

        return $connection->fetchAssoc($select);
    }
}
