<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;

/**
 * Base select processor.
 */
class StockStatusBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StockConfigurationInterface $stockConfig
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param ResourceConnection $resourceConnection
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param StockStatusResource $stockStatusResource
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StockConfigurationInterface $stockConfig,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        ResourceConnection $resourceConnection,
        DefaultStockProviderInterface $defaultStockProvider,
        StockStatusResource $stockStatusResource
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfig = $stockConfig;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->resourceConnection = $resourceConnection;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Improves the select with stock status sub query.
     *
     * @param Select $select
     * @return Select
     * @throws NoSuchEntityException
     */
    public function process(Select $select)
    {
        if (!$this->stockConfig->isShowOutOfStock()) {
            return $select;
        }

        $websiteCode = $this->storeManager->getWebsite()
            ->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = (int)$stock->getStockId();
        if ($stockId === $this->defaultStockProvider->getId()) {
            $stockTable = 'cataloginventory_stock_status';
            $stockTable = $this->resourceConnection->getTableName($stockTable);
            $isSalableColumnName = 'stock_status';

            /** @var Select $select */
            $select->join(
                ['stock' => $stockTable],
                sprintf(
                    'stock.product_id = %s.entity_id',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                ),
                []
            );
        } else {
            $stockTable = $this->stockIndexTableNameResolver->execute($stockId);
            $isSalableColumnName = IndexStructure::IS_SALABLE;

            /** @var Select $select */
            $select->join(
                ['stock' => $stockTable],
                sprintf('stock.sku = %s.sku', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            );
        }

        $select->where(sprintf('stock.%1s = ?', $isSalableColumnName), 1);

        return $select;
    }
}
