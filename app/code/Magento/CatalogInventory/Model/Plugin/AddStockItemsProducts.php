<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

/**
 * Stock Item as a part of ExtensionAttributes
 */
class AddStockItemsProducts
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param array $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterGetItems(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, $result)
    {
        foreach ($result as $product) {
            $productExtension = $product->getExtensionAttributes();
            $productExtension->setStockItem($this->stockRegistry->getStockItem($product->getId()));
            $product->setExtensionAttributes($productExtension);
        }
        return $result;
    }
}
