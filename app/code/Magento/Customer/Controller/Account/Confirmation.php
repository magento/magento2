<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Confirmation. Send confirmation link to specified email
 */
class Confirmation implements HttpGetActionInterface, HttpPostActionInterface, AccountInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountManagementInterface
     */
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
     * @var Url
     */
    private $customerUrl;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $redirectFactory
     * @param StoreManagerInterface $storeManager
     * @param MessageManagerInterface $messageManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param Url $customerUrl
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        PageFactory $resultPageFactory,
        RedirectFactory $redirectFactory,
        StoreManagerInterface $storeManager,
        MessageManagerInterface $messageManager,
        AccountManagementInterface $customerAccountManagement,
        Url $customerUrl
    ) {
        $this->request = $request;
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerUrl;
    }

    /**
     * Send confirmation link to specified email
     *
     * @return ResultRedirect|ResultPage
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $email = $this->request->getPost('email');
        if ($email) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->redirectFactory->create();

            try {
                $this->customerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccessMessage(__('Please check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccessMessage(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Wrong email.'));
                $resultRedirect->setPath('*/*/*', ['email' => $email, '_secure' => true]);
                return $resultRedirect;
            }

            $this->session->setUsername($email);
            $resultRedirect->setPath('*/*/index', ['_secure' => true]);
            return $resultRedirect;
        }

        /** @var ResultPage $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('accountConfirmation')
            ->setEmail($this->request->getParam('email', $email))
            ->setLoginUrl($this->customerUrl->getLoginUrl());
        return $resultPage;
    }
}
