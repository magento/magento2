<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\ApplyStockConditionToSelect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adapt apply stock condition to multi stocks
 */
class AdaptApplyStockConditionToSelectPlugin
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

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
     * @param ResourceConnection $resourceConnection
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Adapt apply stock condition to multi stocks
     *
     * @param ApplyStockConditionToSelect $applyStockConditionToSelect
     * @param callable $proceed
     * @param string $alias
     * @param string $stockAlias
     * @param Select $select
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ApplyStockConditionToSelect $applyStockConditionToSelect,
        callable $proceed,
        string $alias,
        string $stockAlias,
        Select $select
    ) {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        if ($this->defaultStockProvider->getId() === $stock->getStockId()) {
            $proceed($alias, $stockAlias, $select);
        } else {
            $select->joinInner(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                sprintf('product.entity_id = %s.source_id', $alias),
                []
            );
            $tableName = $this->stockIndexTableNameResolver->execute((int)$stock->getStockId());

            $select->joinInner(
                [$stockAlias => $tableName],
                sprintf('product.sku = %s.sku', $stockAlias),
                []
            );
        }
    }
}
