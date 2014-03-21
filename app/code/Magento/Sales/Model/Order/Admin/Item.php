<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
