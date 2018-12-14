<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryElasticsearch\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

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
     * @param Config $config
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        Config $config,
        StockConfigurationInterface $stockConfiguration,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->config = $config;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
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

            foreach ($this->getProductIdsByWebsiteIds($productIds) as $websiteId => $productIds) {
                $stock = $this->stockByWebsiteIdResolver->execute($websiteId);
                $stockTable = $this->stockIndexTableNameResolver->execute((int)$stock->getStockId());

                if ($this->resourceConnection->getConnection()->isTableExists($stockTable)) {
                    $connection = $this->resourceConnection->getConnection();
                    $select = $connection->select();
                    $select->from(['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                        ['entity_id']
                    );
                    $select->joinInner(
                        ['stock' => $stockTable],
                        "product.sku = stock.sku",
                        ['is_salable']
                    );
                    $select->where('product.entity_id IN (?)', $productIds);
                    $productsSalable = $connection->fetchAssoc($select);
                    $productsSalable = array_filter($productsSalable, function ($productSalable) {
                        return StockStatusInterface::STATUS_IN_STOCK == $productSalable['is_salable'];
                    });
                    $stockStatuses += $productsSalable;
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
     * Get all website ids by product ids.
     *
     * @param array $entityIds
     * @return array
     */
    private function getProductIdsByWebsiteIds(array $entityIds): array
    {
        $result = [];

        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['product_in_websites' => $this->resourceConnection->getTableName('catalog_product_website')],
            ['website_id', 'product_id']
        )->where('product_in_websites.product_id IN (?)', $entityIds)->distinct();
        foreach ($connection->fetchAll($select) as $websiteData) {
            $result[(int)$websiteData['website_id']][] = (int)$websiteData['product_id'];
        }

        return $result;
    }
}
