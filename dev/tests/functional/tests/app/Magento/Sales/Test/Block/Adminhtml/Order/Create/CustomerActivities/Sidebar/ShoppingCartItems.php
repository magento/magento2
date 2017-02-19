<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;

/**
 * Chopping cart items block on Sidebar on Create Order page on backend.
 */
class ShoppingCartItems extends Sidebar
{
    /**
     * Shopping Cart items on backend.
     *
     * @var string
     */
    protected $itemName = '//td[@class="col-product"]//span[contains(@class, "title") and normalize-space(text()) = "%s"]';

    /**
     * Get product name from Customer Shopping Cart on backend.
     *
     * @param $product
     * @return string
     */
    public function getItemName($product)
    {
        return $this->_rootElement->find(sprintf($this->itemName, $product->getName()), Locator::SELECTOR_XPATH)->getText();
    }
}
