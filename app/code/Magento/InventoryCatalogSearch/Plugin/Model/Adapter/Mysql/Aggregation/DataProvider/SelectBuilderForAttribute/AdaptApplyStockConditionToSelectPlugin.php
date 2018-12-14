<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\
ApplyStockConditionToSelect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolver;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adapt apply stock condition to multi stocks
 */
class AdaptApplyStockConditionToSelectPlugin
{
    /**
     * @var StockIndexTableNameResolver
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

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
     * @param StockIndexTableNameResolver $stockIndexTableNameResolver
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        StockIndexTableNameResolver $stockIndexTableNameResolver,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param ApplyStockConditionToSelect $applyStockConditionToSelect
     * @param callable $proceed
     * @param Select $select
     * @return Select
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ApplyStockConditionToSelect $applyStockConditionToSelect,
        callable $proceed,
        Select $select
    ): Select {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = (int)$stock->getStockId();

        if ($stockId === $this->defaultStockProvider->getId()) {
            return $proceed($select);
        }

        $tableName = $this->stockIndexTableNameResolver->execute($stockId);
        $select->joinInner(
            ['product' => $this->resource->getTableName('catalog_product_entity')],
            'main_table.source_id = product.entity_id',
            []
        );
        $select->joinInner(
            ['stock_index' => $tableName],
            'product.sku = stock_index.sku',
            []
        )->where('stock_index.' . IndexStructure::IS_SALABLE . ' = ?', 1);

        return $select;
    }
}
