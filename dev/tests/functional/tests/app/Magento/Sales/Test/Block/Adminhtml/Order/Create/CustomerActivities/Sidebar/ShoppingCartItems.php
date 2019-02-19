<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;

/**
 * Shopping cart items block on Sidebar on Create Order page on backend.
 */
class ShoppingCartItems extends Sidebar
{
    /**
     * Shopping Cart items on backend.
     *
     * @var string
     */
    protected $itemName = '//td[@class="col-product"]//span[contains(@class,"title") and normalize-space(text())="%s"]';

    /**
     * Get product name from Customer Shopping Cart on backend.
     *
     * @param InjectableFixture $product
     * @return string
     */
    public function getItemName(InjectableFixture $product)
    {
        return $this->_rootElement
            ->find(sprintf($this->itemName, $product->getName()), Locator::SELECTOR_XPATH)->getText();
    }
}
