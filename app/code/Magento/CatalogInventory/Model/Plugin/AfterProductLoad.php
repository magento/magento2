<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

/**
 * @deprecated 100.2.0 Stock Item as a part of ExtensionAttributes is deprecated
 * @see StockItemInterface when you want to change the stock data
 * @see StockStatusInterface when you want to read the stock data for representation layer (storefront)
 * @see StockItemRepositoryInterface::save as extension point for customization of saving process
 */
class AfterProductLoad
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Add stock item information to the product's extension attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterLoad(\Magento\Catalog\Model\Product $product)
    {
        $productExtension = $product->getExtensionAttributes();
        $productExtension->setStockItem($this->stockRegistry->getStockItem($product->getId()));
        $product->setExtensionAttributes($productExtension);
        return $product;
    }
}
