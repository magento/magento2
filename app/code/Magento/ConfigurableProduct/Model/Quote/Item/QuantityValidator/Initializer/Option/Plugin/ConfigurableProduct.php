<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

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

    /**
     * Keep the backordered quantity of the selected variant on the configurable quote item
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject
     * @param \Magento\Framework\DataObject $result
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject,
        \Magento\Framework\DataObject $result,
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        if ($quoteItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            && $result->getItemBackorders() !== null
        ) {
            $quoteItem->setBackorders($result->getItemBackorders());
        }
        return $result;
    }
}
