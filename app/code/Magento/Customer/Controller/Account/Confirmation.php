<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;

class Confirmation extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var AccountManagementInterface  */
    protected $customerAccountManagement;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            try {
                $this->customerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccess(__('Please check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Wrong email.'));
                $resultRedirect->setPath('*/*/*', ['email' => $email, '_secure' => true]);
                return $resultRedirect;
            }
            $this->session->setUsername($email);
            $resultRedirect->setPath('*/*/index', ['_secure' => true]);
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('accountConfirmation')->setEmail(
            $this->getRequest()->getParam('email', $email)
        );
        return $resultPage;
    }
}
