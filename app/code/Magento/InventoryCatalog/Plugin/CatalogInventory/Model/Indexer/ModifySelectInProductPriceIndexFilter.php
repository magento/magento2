<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

/**
 * Delete not available products from price index temporary table by website.
 */
class ModifySelectInProductPriceIndexFilter
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StockConfigurationInterface $stockConfiguration
     * @param ResourceConnection $resourceConnection
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StockConfigurationInterface $stockConfiguration,
        ResourceConnection $resourceConnection,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfiguration = $stockConfiguration;
        $this->resourceConnection = $resourceConnection;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Modify parent select. Add MSI stock table to select.
     *
     * @param ProductPriceIndexFilter $subject
     * @param callable $closure
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundModifyPrice(
        ProductPriceIndexFilter $subject,
        callable $closure,
        IndexTableStructure $priceTable,
        array $entityIds = []
    ): void {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        foreach ($this->getWebsiteIdsFromProducts($entityIds) as $websiteId) {
            $stock = $this->stockByWebsiteIdResolver->execute($websiteId);
            $stockTable = $this->stockIndexTableNameResolver->execute((int)$stock->getStockId());
            $connection = $this->resourceConnection->getConnection('indexer');
            $select = $connection->select();
            $select->from(['price_index' => $priceTable->getTableName()], []);
            $priceEntityField = $priceTable->getEntityField();

            if (!$this->isDefaultStock($stock) && $this->resourceConnection->getConnection()->isTableExists($stockTable)) {
                $select->joinInner(
                    ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                    "product_entity.entity_id = price_index.{$priceEntityField}",
                    []
                )->joinLeft(
                    ['inventory_stock' => $stockTable],
                    'inventory_stock.sku = product_entity.sku',
                    []
                );
                $select->where('inventory_stock.is_salable = 0 OR inventory_stock.is_salable IS NULL');
            } else {
                $legacyStockTableName = $this->resourceConnection->getTableName('cataloginventory_stock_status');
                $select->joinLeft(
                    ['stock_status' => $legacyStockTableName],
                    sprintf(
                        'stock_status.product_id = price_index.%s and stock_status.website_id = %d',
                        $priceEntityField,
                        $websiteId
                    ),
                    []
                );
                $select->where('stock_status.stock_status = 0 OR stock_status.stock_status IS NULL');
            }

            $select->where('price_index.website_id = ?', $websiteId);
            $select->where("price_index.{$priceEntityField} IN (?)", $entityIds);
            $query = $select->deleteFromSelect('price_index');
            $connection->query($query);
        }
    }

    /**
     * Get all website ids by product ids.
     *
     * @param array $entityIds
     * @return array
     */
    private function getWebsiteIdsFromProducts(array $entityIds): array
    {
        $result = [];

        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['product_in_websites' => $this->resourceConnection->getTableName('catalog_product_website')],
            ['website_id']
        )->where('product_in_websites.product_id IN (?)', $entityIds)->distinct();
        foreach ($connection->fetchCol($select) as $websiteId) {
            $result[] = (int)$websiteId;
        }

        return $result;
    }

    /**
     * Checks if inventory stock is DB view
     *
     * @param StockInterface $stock
     * @return bool
     */
    private function isDefaultStock(StockInterface $stock): bool
    {
        return (int)$stock->getStockId() === $this->defaultStockProvider->getId();
    }
}
