<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add stock item filter to selects.
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
    private $resource;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StockConfigurationInterface $stockConfig
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param ResourceConnection $resource
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StockConfigurationInterface $stockConfig,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        ResourceConnection $resource,
        DefaultStockProviderInterface $defaultStockProvider = null
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfig = $stockConfig;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->resource = $resource;
        $this->defaultStockProvider = $defaultStockProvider ?: ObjectManager::getInstance()
            ->get(DefaultStockProviderInterface::class);
    }

    /**
     * @param Select $select
     * @return Select
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function process(Select $select)
    {
        if (!$this->stockConfig->isShowOutOfStock()) {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = (int)$stock->getStockId();
            if ($stockId === $this->defaultStockProvider->getId()) {
                $stockTable = $this->resource->getTableName('cataloginventory_stock_status');
                $isSalableColumnName = 'stock_status';

                /** @var Select $select */
                $select->join(
                    ['stock' => $stockTable],
                    sprintf('stock.product_id = %s.entity_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
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
        }

        return $select;
    }
}
