<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;

class ForgotPasswordPost extends \Magento\Customer\Controller\Account
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Escaper */
    protected $escaper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        parent::__construct($context, $customerSession, $resultPageFactory);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->messageManager->addError(__('Please correct the email address.'));
                $resultRedirect->setPath('*/*/forgotpassword');
                return $resultRedirect;
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $e) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('We\'re unable to send the password reset email.'));
                $resultRedirect->setPath('*/*/forgotpassword');
                return $resultRedirect;
            }
            // @codingStandardsIgnoreStart
            $this->messageManager->addSuccess(__('We\'ll email you a link to reset your password.'));
            // @codingStandardsIgnoreEnd
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        } else {
            $this->messageManager->addError(__('Please enter your email.'));
            $resultRedirect->setPath('*/*/forgotpassword');
            return $resultRedirect;
        }
    }
}
