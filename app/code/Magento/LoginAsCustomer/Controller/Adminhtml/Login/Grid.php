<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * LoginAsCustomer log grid action
 */
class Grid extends \Magento\Backend\App\Action
{
    /**
     * Login as customer log grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_LoginAsCustomer::login_log');
    }
}
