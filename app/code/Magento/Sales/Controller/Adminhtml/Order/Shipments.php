<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class Shipments extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Generate shipments grid for ajax request
     *
     * @return void
     */
    public function execute()
    {
        $this->_initOrder();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
