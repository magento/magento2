<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class Logout implements HttpGetActionInterface, HttpPostActionInterface, AccountInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     * @param RedirectFactory $redirectFactory
     * @param RedirectInterface $redirect
     * @param Session $customerSession
     */
    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        RedirectFactory $redirectFactory,
        RedirectInterface $redirect,
        Session $customerSession
    ) {
        $this->session = $customerSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->redirectFactory = $redirectFactory;
        $this->redirect = $redirect;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Customer logout action
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        $lastCustomerId = $this->session->getId();
        $this->session->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);

        if ($this->cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath('*/*/logoutSuccess');
        return $resultRedirect;
    }
}
