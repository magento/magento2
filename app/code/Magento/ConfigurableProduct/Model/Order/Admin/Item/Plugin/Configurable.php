<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin;

/**
 * Class \Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin\Configurable
 *
 * @since 2.0.0
 */
class Configurable
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
