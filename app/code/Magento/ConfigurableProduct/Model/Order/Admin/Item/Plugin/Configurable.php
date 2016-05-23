<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin;

class Configurable
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(\Magento\Catalog\Model\ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * Get item sku
     *
     * @param \Magento\Sales\Model\Order\Admin\Item $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetSku(
        \Magento\Sales\Model\Order\Admin\Item $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Item $item
    ) {
        if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productOptions = $item->getProductOptions();
            return $productOptions['simple_sku'];
        }

        return $proceed($item);
    }

    /**
     * Get item name
     *
     * @param \Magento\Sales\Model\Order\Admin\Item $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetName(
        \Magento\Sales\Model\Order\Admin\Item $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Item $item
    ) {
        if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productOptions = $item->getProductOptions();
            return $productOptions['simple_name'];
        }

        return $proceed($item);
    }

    /**
     * Get product id
     *
     * @param \Magento\Sales\Model\Order\Admin\Item $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetProductId(
        \Magento\Sales\Model\Order\Admin\Item $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Item $item
    ) {
        if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productOptions = $item->getProductOptions();
            $product = $this->productFactory->create();
            return $product->getIdBySku($productOptions['simple_sku']);
        }
        return $proceed($item);
    }
}
