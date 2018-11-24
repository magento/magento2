<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Model\Customer\CredentialsValidator;

class ResetPasswordPost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CredentialsValidator|null $credentialsValidator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        CredentialsValidator $credentialsValidator = null
    ) {
        $this->session = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath(
                '*/*/createPassword',
                ['token' => $resetPasswordToken]
            );
            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('Please enter a new password.'));
            $resultRedirect->setPath(
                '*/*/createPassword',
                ['token' => $resetPasswordToken]
            );
            return $resultRedirect;
        }

        try {
            $this->accountManagement->resetPassword(
                '',
                $resetPasswordToken,
                $password
            );
            $this->session->unsRpToken();
            $this->messageManager->addSuccess(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');
            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Something went wrong while saving the new password.'));
        }

        $resultRedirect->setPath(
            '*/*/createPassword',
            ['token' => $resetPasswordToken]
        );
        return $resultRedirect;
    }
}
