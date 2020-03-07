<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\EmailAddress;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPost implements HttpPostActionInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param MessageManagerInterface $messageManager
     * @param Escaper $escaper
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        MessageManagerInterface $messageManager,
        Escaper $escaper
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * Forgot customer password action
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->request->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, EmailAddress::class)) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET);
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
            } catch (Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            return $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}
