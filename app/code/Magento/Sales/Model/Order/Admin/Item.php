<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Admin;

/**
 * Class \Magento\Sales\Model\Order\Admin\Item
 *
 * @since 2.0.0
 */
class Item
{
    /**
     * Get item sku
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     * @since 2.0.0
     */
    public function getSku(\Magento\Sales\Model\Order\Item $item)
    {
        return $item->getSku();
    }

    /**
     * Get item name
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     * @since 2.0.0
     */
    public function getName(\Magento\Sales\Model\Order\Item $item)
    {
        return $item->getName();
    }

    /**
     * Get product id
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return int
     * @since 2.0.0
     */
    public function getProductId(\Magento\Sales\Model\Order\Item $item)
    {
        return $item->getProductId();
    }
}
