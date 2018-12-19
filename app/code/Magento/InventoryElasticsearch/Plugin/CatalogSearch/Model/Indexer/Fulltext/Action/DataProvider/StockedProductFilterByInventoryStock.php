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
     * @param Config $config
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        Config $config,
        StockConfigurationInterface $stockConfiguration,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        StockStatusRepositoryInterface $stockStatusRepository,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->config = $config;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Filter out of stock options for configurable product.
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
        array $indexData,
        array $productData,
        int $storeId
    ): array {
        if ($this->config->isElasticsearchEnabled() && !$this->stockConfiguration->isShowOutOfStock($storeId)) {
            $productIds = array_keys($indexData);
            $stockStatuses = [];

            foreach ($this->getProductIdsByWebsiteIds($productIds) as $websiteId => $productIdsStr) {
                $stock = $this->stockByWebsiteIdResolver->execute($websiteId);
                $stockId = (int)$stock->getStockId();
                $productIdsByWebsite = explode(',', $productIdsStr);

                if ($this->defaultStockProvider->getId() === $stockId) {
                    $stockStatuses += $this->getStockStatusesFromDefaultStock($productIdsByWebsite);
                } else {
                    $stockStatuses += $this->getStockStatusesFromCustomStock($productIdsByWebsite, $stockId);
                }
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
            'product.sku = stock.sky',
            ['is_salable']
        );
        $select->where('product.entity_id IN (?)', $productIds);
        $select->where('stock.is_salable = ?', 1);

        return $connection->fetchAssoc($select);
    }

    /**
     * Get all website ids by product ids.
     *
     * @param array $entityIds
     * @return array
     */
    private function getProductIdsByWebsiteIds(array $entityIds): array
    {
        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['product_in_websites' => $this->resourceConnection->getTableName('catalog_product_website')],
            [
                'website_id',
                'GROUP_CONCAT(product_in_websites.product_id)'
            ]
        )->where('product_in_websites.product_id IN (?)', $entityIds)
            ->group('product_in_websites.website_id');

        return $connection->fetchPairs($select);
    }
}
