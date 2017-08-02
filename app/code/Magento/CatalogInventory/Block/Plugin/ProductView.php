<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Block\Plugin;

use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Class \Magento\CatalogInventory\Block\Plugin\ProductView
 *
 * @since 2.0.0
 */
class ProductView
{
    /**
     * @var StockRegistryInterface
     * @since 2.0.0
     */
    private $stockRegistry;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @since 2.0.0
     */
    public function __construct(
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View $block
     * @param array $validators
     * @return array
     * @since 2.0.0
     */
    public function afterGetQuantityValidators(
        \Magento\Catalog\Block\Product\View $block,
        array $validators
    ) {
        $stockItem = $this->stockRegistry->getStockItem(
            $block->getProduct()->getId(),
            $block->getProduct()->getStore()->getWebsiteId()
        );

        $params = [];
        $params['minAllowed']  = (float)$stockItem->getMinSaleQty();
        if ($stockItem->getQtyMaxAllowed()) {
            $params['maxAllowed'] = $stockItem->getQtyMaxAllowed();
        }
        if ($stockItem->getQtyIncrements() > 0) {
            $params['qtyIncrements'] = (float)$stockItem->getQtyIncrements();
        }
        $validators['validate-item-quantity'] = $params;

        return $validators;
    }
}
