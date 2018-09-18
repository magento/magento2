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
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StockConfigurationInterface $stockConfiguration
     * @param ResourceConnection $resourceConnection
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StockConfigurationInterface $stockConfiguration,
        ResourceConnection $resourceConnection,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfiguration = $stockConfiguration;
        $this->resourceConnection = $resourceConnection;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
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

            if ($this->resourceConnection->getConnection()->isTableExists($stockTable)) {
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
}
