<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;

/**
 * Class CreatePassword
 *
 * @package Magento\Customer\Controller\Account
 */
class CreatePassword extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
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
     * @var \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken
     */
    private $confirmByToken;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken $confirmByToken
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        ConfirmCustomerByToken $confirmByToken = null
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->confirmByToken = $confirmByToken
            ?? ObjectManager::getInstance()->get(ConfirmCustomerByToken::class);

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
        $isDirectLink = $resetPasswordToken != '';
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
        }

        try {
            $this->accountManagement->validateResetPasswordLinkToken(null, $resetPasswordToken);

            $this->confirmByToken->execute($resetPasswordToken);

            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');

                return $resultRedirect;
            } else {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()
                           ->getBlock('resetPassword')
                           ->setResetPasswordLinkToken($resetPasswordToken);

                return $resultPage;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');

            return $resultRedirect;
        }
    }
}
