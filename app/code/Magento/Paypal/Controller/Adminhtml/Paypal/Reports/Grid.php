<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

/**
 * Class \Magento\Paypal\Controller\Adminhtml\Paypal\Reports\Grid
 *
 */
class Grid extends \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
{
    /**
     * Ajax callback for grid actions
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
