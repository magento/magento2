<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

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
