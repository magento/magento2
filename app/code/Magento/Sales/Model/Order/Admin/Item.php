<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Admin;

class Item
{
    /**
     * Get item sku
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
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
     */
    public function getProductId(\Magento\Sales\Model\Order\Item $item)
    {
        return $item->getProductId();
    }
}
