<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Controller\Adminhtml\Login;

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
        return $this->_authorization->isAllowed('Magefan_LoginAsCustomer::login_log');
    }
}
