<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\LoginAsCustomer\Controller\Login;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magefan\LoginAsCustomer\Model\Login
     */
    protected $login = null;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\LoginAsCustomer\Model\Login|null $login
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\LoginAsCustomer\Model\Login $login = null
    ) {
        parent::__construct($context);
        $objectManager = $this->_objectManager;
        $this->login = $login ?: $objectManager->get(\Magefan\LoginAsCustomer\Model\Login::class);
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
            $this->messageManager->addSuccess(
                __('You are logged in as customer: %1', $login->getCustomer()->getName())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('*/*/proceed');
    }

    /**
     * Init login info
     * @return false || \Magefan\LoginAsCustomer\Model\Login
     */
    protected function _initLogin()
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            $this->messageManager->addError(__('Cannot login to account. No secret key provided.'));
            return false;
        }

        $login = $this->login->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            $this->messageManager->addError(__('Cannot login to account. Secret key is not valid.'));
            return false;
        }
    }
}
