<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

class Index extends \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Paypal::paypal_settlement_reports_view';

    /**
     * Grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->renderLayout();
    }
}
