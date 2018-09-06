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
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StockConfigurationInterface $stockConfiguration
     * @param ResourceConnection $resourceConnection
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StockConfigurationInterface $stockConfiguration,
        ResourceConnection $resourceConnection,
        DefaultStockProviderInterface $defaultStockProvider,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfiguration = $stockConfiguration;
        $this->resourceConnection = $resourceConnection;
        $this->defaultStockProvider = $defaultStockProvider;
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

        $tmpPriceTableName = $priceTable->getTableName();

        foreach ($this->getWebsiteIdsFromTmpTable($tmpPriceTableName) as $websiteId) {
            $stock = $this->stockByWebsiteIdResolver->execute($websiteId);
            $stockId = (int)$stock->getStockId();
            $stockTable = $this->stockIndexTableNameResolver->execute($stockId);

            $connection = $this->resourceConnection->getConnection('indexer');
            $select = $connection->select();
            $select->from(['price_index' => $tmpPriceTableName], []);

            if ($stockId === $this->defaultStockProvider->getId()) {
                $select->joinLeft(
                    ['stock_item' => $stockTable],
                    'stock_item.product_id = price_index.' . $priceTable->getEntityField()
                    . ' AND stock_item.stock_id = ' . $stockId,
                    []
                );
            } else {
                $select->joinInner(
                    ['product_entity' => $connection->getTableName('catalog_product_entity')],
                    'product_entity.entity_id = price_index.' . $priceTable->getEntityField(),
                    []
                )->joinLeft(
                    ['stock_item' => $stockTable],
                    'stock_item.sku = product_entity.sku',
                    []
                );
            }

            $select->where('stock_item.is_salable = 0 OR stock_item.is_salable IS NULL');
            $select->where('price_index.website_id = ?', $websiteId);
            $query = $select->deleteFromSelect('price_index');
            $connection->query($query);
        }
    }

    /**
     * Get all website ids from price temporary table.
     *
     * @param string $tmpPriceTableName
     * @return array
     */
    private function getWebsiteIdsFromTmpTable(string $tmpPriceTableName): array
    {
        $result = [];

        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['price_index' => $tmpPriceTableName],
            ['website_id']
        )->distinct();
        foreach ($connection->fetchCol($select) as $websiteId) {
            $result[] = (int)$websiteId;
        }

        return $result;
    }
}
