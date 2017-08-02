<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product quote initializer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\Sales\Model\AdminOrder\Product\Quote;

/**
 * Class \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer
 *
 * @since 2.0.0
 */
class Initializer
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Quote\Model\Quote\Item|string
     * @since 2.0.0
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
