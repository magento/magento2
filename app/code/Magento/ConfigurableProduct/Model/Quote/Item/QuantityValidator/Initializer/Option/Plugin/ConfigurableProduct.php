<?php
/**
 * Stock item initializer for configurable product type
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

/**
 * Class \Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin\ConfigurableProduct
 *
 */
class ConfigurableProduct
{
    /**
     * Initialize stock item for configurable product type
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject
     * @param \Magento\CatalogInventory\Model\Stock\Item $stockItem
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetStockItem(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem,
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        if ($quoteItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $stockItem->setProductName($quoteItem->getName());
        }
        return $stockItem;
    }
}
