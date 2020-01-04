<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LoginAsCustomer\Controller\Login;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    protected $loginModel = null;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\LoginAsCustomer\Model\Login|null $loginModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\LoginAsCustomer\Model\Login $loginModel = null
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel ?: $this->_objectManager->get(\Magento\LoginAsCustomer\Model\Login::class);
    }
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $login = $this->_initLogin();
        if (!$login) {
            $this->_redirect('/');
            return;
        }

        try {
            /* Log in */
            $login->authenticateCustomer();
            $this->messageManager->addSuccessMessage(
                __('You are logged in as customer: %1', $login->getCustomer()->getName())
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('*/*/proceed');
    }

    /**
     * Init login info
     * @return false || \Magento\LoginAsCustomer\Model\Login
     */
    protected function _initLogin()
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            $this->messageManager->addErrorMessage(__('Cannot login to account. No secret key provided.'));
            return false;
        }

        $login = $this->loginModel->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            $this->messageManager->addErrorMessage(__('Cannot login to account. Secret key is not valid.'));
            return false;
        }
    }
}
