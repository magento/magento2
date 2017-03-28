<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class CreatePassword extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var AccountManagementInterface */
    protected $accountManagement;

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
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        parent::__construct($context);
    }

    /**
     * Resetting password handler
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $customerId = (int)$this->getRequest()->getParam('id');
        $isDirectLink = $resetPasswordToken != '' && $customerId != 0;
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
            $customerId = (int)$this->session->getRpCustomerId();
        }

        try {
            $this->accountManagement->validateResetPasswordLinkToken($customerId, $resetPasswordToken);

            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $this->session->setRpCustomerId($customerId);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');
                return $resultRedirect;
            } else {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()->getBlock('resetPassword')->setCustomerId($customerId)
                    ->setResetPasswordLinkToken($resetPasswordToken);
                return $resultPage;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');
            return $resultRedirect;
        }
    }
}
