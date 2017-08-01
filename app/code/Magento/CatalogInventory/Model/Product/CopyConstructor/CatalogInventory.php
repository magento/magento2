<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Product\CopyConstructor;

/**
 * Class \Magento\CatalogInventory\Model\Product\CopyConstructor\CatalogInventory
 *
 * @since 2.0.0
 */
class CatalogInventory implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Copy product inventory data (used for product duplicate functionality)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     * @since 2.0.0
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        $stockData = [
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
        ];
        $currentStockItemDo = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        if ($currentStockItemDo->getItemId()) {
            $stockData += [
                'use_config_enable_qty_inc' => $currentStockItemDo->getUseConfigEnableQtyInc(),
                'enable_qty_increments' => $currentStockItemDo->getEnableQtyIncrements(),
                'use_config_qty_increments' => $currentStockItemDo->getUseConfigQtyIncrements(),
                'qty_increments' => $currentStockItemDo->getQtyIncrements(),
            ];
        }
        $duplicate->setStockData($stockData);
    }
}
