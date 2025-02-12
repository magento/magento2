<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Send confirmation link to specified email
 */
class Confirmation extends AbstractAccount implements HttpGetActionInterface, HttpPostActionInterface
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
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param Url|null $customerUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        ?Url $customerUrl = null
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerUrl ?: ObjectManager::getInstance()->get(Url::class);
        parent::__construct($context);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return Redirect|Page
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            return $this->getRedirect('*/*/');
        }

        $email = $this->getRequest()->getPost('email');

        if ($email) {
            try {
                $this->customerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccessMessage(__('Please check your email for confirmation key.'));
                return $this->getRedirect('*/*/index', ['_secure' => true]);
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccessMessage(__('This email does not require confirmation.'));
                return $this->getRedirect('*/*/index', ['_secure' => true]);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Wrong email.'));
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('accountConfirmation')
            ->setEmail($email)
            ->setLoginUrl($this->customerUrl->getLoginUrl());
        return $resultPage;
    }

    /**
     * Returns redirect object
     *
     * @param string $path
     * @param array $params
     * @return Redirect
     */
    private function getRedirect(string $path, array $params = []): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path, $params);

        return $resultRedirect;
    }
}
