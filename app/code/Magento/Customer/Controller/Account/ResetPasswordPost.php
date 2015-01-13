<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class ResetPasswordPost extends \Magento\Customer\Controller\Account
{
    /** @var AccountManagementInterface */
    protected $accountManagement;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $customerSession);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return void
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $customerId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            return;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('New password field cannot be empty.'));
            $this->_redirect('*/*/createPassword', ['id' => $customerId, 'token' => $resetPasswordToken]);
            return;
        }

        try {
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
            $this->accountManagement->resetPassword($customerEmail, $resetPasswordToken, $password);
            $this->messageManager->addSuccess(__('Your password has been updated.'));
            $this->_redirect('*/*/login');
            return;
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('There was an error saving the new password.'));
            $this->_redirect('*/*/createPassword', ['id' => $customerId, 'token' => $resetPasswordToken]);
            return;
        }
    }
}
