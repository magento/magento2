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
 * @since 2.0.0
 */
class Grid extends \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
{
    /**
     * Ajax callback for grid actions
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
