<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;

class Confirmation extends \Magento\Customer\Controller\Account
{
    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var AccountManagementInterface  */
    protected $customerAccountManagement;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        UrlFactory $urlFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context, $customerSession);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $this->customerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccess(__('Please, check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Wrong email.'));
                $this->getResponse()->setRedirect(
                    $this->urlModel->getUrl('*/*/*', ['email' => $email, '_secure' => true])
                );
                return;
            }
            $this->_getSession()->setUsername($email);
            $this->getResponse()->setRedirect($this->urlModel->getUrl('*/*/index', ['_secure' => true]));
            return;
        }

        // output form
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock(
            'accountConfirmation'
        )->setEmail(
            $this->getRequest()->getParam('email', $email)
        );

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
