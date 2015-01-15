<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart\Sidebar;

use Magento\Checkout\Test\Block\Cart\Sidebar;

/**
 * Class MiniCartItem
 * Product item block on mini Cart
 */
class Item extends Sidebar
{
    /**
     * Selector for "Remove item" button
     *
     * @var string
     */
    protected $removeItem = '.action.delete';

    /**
     * Selector for "Edit item" button
     *
     * @var string
     */
    protected $editItem = '.action.edit';

    /**
     * Remove product item from mini cart
     *
     * @return void
     */
    public function removeItemFromMiniCart()
    {
        $this->_rootElement->find($this->removeItem)->click();
        $this->_rootElement->acceptAlert();
    }

    /**
     * Click "Edit item" button
     *
     * @return void
     */
    public function clickEditItem()
    {
        $this->_rootElement->find($this->editItem)->click();
    }
}
