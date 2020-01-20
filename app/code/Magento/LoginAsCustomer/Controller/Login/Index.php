<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Login;

use Magento\Framework\Exception\LocalizedException;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    private $loginModel = null;

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
        try {
            $login = $this->_initLogin();

            /* Log in */
            $login->authenticateCustomer();
            $this->messageManager->addSuccessMessage(
                __('You are logged in as customer: %1', $login->getCustomer()->getName())
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('/');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('*/*/proceed');
    }

    /**
     * Init login info
     * @return \Magento\LoginAsCustomer\Model\Login
     */
    private function _initLogin(): \Magento\LoginAsCustomer\Model\Login
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            throw LocalizedException(__('Cannot login to account. No secret key provided.'));
        }

        $login = $this->loginModel->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            throw LocalizedException(__('Cannot login to account. Secret key is not valid'));
        }
    }
}
