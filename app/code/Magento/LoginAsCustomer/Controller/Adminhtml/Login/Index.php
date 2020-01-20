<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * LoginAsCustomer log action
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login_log';

    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    private $loginModel;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\LoginAsCustomer\Model\Login $loginModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\LoginAsCustomer\Model\Login $loginModel
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel;
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
}
