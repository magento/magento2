<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

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
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StockConfigurationInterface $stockConfig
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StockConfigurationInterface $stockConfig,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfig = $stockConfig;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
    }

    /**
     * @param Select $select
     * @return Select
     */
    public function process(Select $select)
    {
        if (!$this->stockConfig->isShowOutOfStock()) {
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            $stockTable = $this->stockIndexTableNameResolver->execute($stockId);

            /** @var Select $select */
            $select->join(
                ['stock' => $stockTable],
                sprintf('stock.sku = parent.sku'),
                []
            )->where('stock.quantity > 0');
            //todo https://github.com/magento-engcom/msi/pull/442 )->where('stock.is_salable = ?', 1);
        }

        return $select;
    }
}
