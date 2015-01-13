<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class Transactions extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Order transactions grid ajax action
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
