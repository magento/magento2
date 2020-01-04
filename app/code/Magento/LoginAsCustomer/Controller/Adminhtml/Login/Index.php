<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * LoginAsCustomer log action
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    protected $loginModel = null;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\LoginAsCustomer\Model\Login|null $login
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\LoginAsCustomer\Model\Login $loginModel = null
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel ?: $this->_objectManager->get(\Magento\LoginAsCustomer\Model\Login::class);
    }
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

        $this->loginModel->deleteNotUsed();

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_LoginAsCustomer::login_log');
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
        return $this->_authorization->isAllowed('Magento_LoginAsCustomer::login_log');
    }
}
