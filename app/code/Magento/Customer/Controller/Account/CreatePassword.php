<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;

class CreatePassword implements HttpGetActionInterface, AccountInterface
{
    /**
     * @var AccountManagementInterface
     */
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
     * @var ConfirmCustomerByToken
     */
    private $confirmByToken;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;
    
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $redirectFactory
     * @param AccountManagementInterface $accountManagement
     * @param ConfirmCustomerByToken $confirmByToken
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        PageFactory $resultPageFactory,
        RedirectFactory $redirectFactory,
        AccountManagementInterface $accountManagement,
        ConfirmCustomerByToken $confirmByToken,
        MessageManagerInterface $messageManager
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->confirmByToken = $confirmByToken;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
    }

    /**
     * Resetting password handler
     *
     * @return ResultRedirect|ResultPage
     */
    public function execute()
    {
        $resetPasswordToken = $this->getResetPasswordToken();

        try {
            $this->accountManagement->validateResetPasswordLinkToken(null, $resetPasswordToken);

            $this->confirmByToken->execute($resetPasswordToken);

            if ($resetPasswordToken) {
                $this->session->setRpToken($resetPasswordToken);
                $resultRedirect = $this->redirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');

                return $resultRedirect;
            }

            /** @var ResultPage $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()
                ->getBlock('resetPassword')
                ->setResetPasswordLinkToken($resetPasswordToken);

            return $resultPage;
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');

            return $resultRedirect;
        }
    }

    /**
     * Returns Reset Password Token from request or if missing, from Session
     *
     * @return string
     */
    private function getResetPasswordToken(): string
    {
        $resetPasswordToken = $this->request->getParam('token', '');

        if (!$resetPasswordToken) {
            $resetPasswordToken = $this->session->getRpToken();
        }

        return (string)$resetPasswordToken;
    }
}
