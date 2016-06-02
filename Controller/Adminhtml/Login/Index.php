<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * LoginAsCustomer log action
 */
class Index extends \Magento\Backend\App\Action
{
	/**
     * Login as customer log
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_objectManager
            ->create('\Magefan\LoginAsCustomer\Model\Login')
            ->deleteNotUsed();

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magefan_LoginAsCustomer::login_log');
        $title = __('Login As Customer Log ');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
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
