<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * CSS selector for qty field.
     *
     * @var string
     */
    private $qty = 'input.cart-item-qty';

    /**
     * CSS selector for update button.
     *
     * @var string
     */
    private $updateButton = 'button.update-cart-item';

    /**
     * Remove product item from mini cart
     *
     * @return void
     */
    public function removeItemFromMiniCart()
    {
        $this->_rootElement->find($this->removeItem)->click();
        $element = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $element]);
        $modal->acceptAlert();
        $modal->waitModalWindowToDisappear();
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

    /**
     * Edit qty.
     *
     * @param array $checkoutData
     * @return void
     */
    public function editQty(array $checkoutData)
    {
        if (isset($checkoutData['qty'])) {
            $this->_rootElement->find($this->qty)->setValue($checkoutData['qty']);
            $this->_rootElement->find($this->updateButton)->click();
        }
    }
}
