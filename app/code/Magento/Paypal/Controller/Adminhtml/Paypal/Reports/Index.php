<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

/**
 * Class \Magento\Paypal\Controller\Adminhtml\Paypal\Reports\Index
 *
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->renderLayout();
    }
}
