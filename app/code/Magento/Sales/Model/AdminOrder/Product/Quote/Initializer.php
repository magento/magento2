<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product quote initializer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\Sales\Model\AdminOrder\Product\Quote;

class Initializer
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Quote\Model\Quote\Item|string
     */
    public function init(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $config
    ) {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $quote->getStore()->getWebsiteId());
        if ($stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int)$config->getQty());
        }

        $product->setCartQty($config->getQty());

        $item = $quote->addProduct(
            $product,
            $config,
            \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
        );

        return $item;
    }
}
