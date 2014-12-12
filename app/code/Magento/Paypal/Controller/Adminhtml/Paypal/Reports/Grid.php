<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
