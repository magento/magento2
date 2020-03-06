<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class ResetPasswordPost implements HttpPostActionInterface, AccountInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param MessageManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        MessageManagerInterface $messageManager,
        RedirectFactory $redirectFactory
    ) {
        $this->session = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        try {
            $this->validatePassword();
            $this->accountManagement->resetPassword(null, $this->getResetPasswordToken(), $this->getPassword());
            $this->session->unsRpToken();
            $this->messageManager->addSuccessMessage(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');

            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the new password.'));
        }
        $resultRedirect->setPath('*/*/createPassword', ['token' => $this->getResetPasswordToken()]);

        return $resultRedirect;
    }

    /**
     * Validates requested Password data
     *
     * @return void
     * @throws InputException
     */
    private function validatePassword(): void
    {
        if ($this->getPassword() !== $this->getPasswordConfirmation()) {
            throw new InputException(__("New Password and Confirm New Password values didn't match."));
        }

        if (iconv_strlen($this->getPassword()) <= 0) {
            throw new InputException(__('Please enter a new password.'));
        }
    }

    /**
     * Returns `password` value from Request object
     *
     * @return string
     */
    private function getPassword(): string
    {
        return (string)$this->request->getPost('password');
    }

    /**
     * Returns `password_confirmation` value from Request object
     *
     * @return string
     */
    private function getPasswordConfirmation(): string
    {
        return (string)$this->request->getPost('password_confirmation');
    }

    /**
     * Returns `token` value from Request object
     *
     * @return string
     */
    private function getResetPasswordToken(): string
    {
        return (string)$this->request->getQuery('token');
    }
}
