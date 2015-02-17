<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart;

class Index extends \Magento\Checkout\Controller\Cart
{
    /**
     * Shopping cart display action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $layout->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Shopping Cart'));
        $this->_view->renderLayout();
    }
}
