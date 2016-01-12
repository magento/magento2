<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Security\Model\AdminSessionsManager;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Backend\Controller\Adminhtml\Auth\Login;

/**
 * Magento\Backend\Controller\Adminhtml\Auth\Login decorator
 */
class LoginController
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $phpCookieManager;

    /**
     * @var CookieReaderInterface
     */
    protected $cookieReader;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendData;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager
     * @param CookieReaderInterface $cookieReader
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager,
        CookieReaderInterface $cookieReader,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->phpCookieManager = $phpCookieManager;
        $this->cookieReader = $cookieReader;
        $this->backendData = $backendData;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param Login $login
     * @return void
     */
    public function beforeExecute(Login $login)
    {
        $logoutReasonCode = $this->cookieReader->getCookie(AuthSession::LOGOUT_REASON_CODE_COOKIE_NAME, -1);
        if ($this->isLoginForm($login) && $logoutReasonCode >= 0) {
            $this->messageManager->addError(
                $this->sessionsManager->getLogoutReasonMessageByStatus($logoutReasonCode)
            );
            $this->deleteLogoutReasonCookie();
        }
    }

    /**
     * Check if the login form action is requested directly
     *
     * @param Login $login
     * @return bool
     */
    protected function isLoginForm(Login $login)
    {
        return $login->getRequest()->getUri() == $login->getUrl('*');
    }

    /**
     * @return $this
     */
    protected function deleteLogoutReasonCookie()
    {
        /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $metaData */
        $metaData = $this->cookieMetadataFactory->create();
        $metaData->setPath('/' . $this->backendData->getAreaFrontName())->setDuration(-1);

        $this->phpCookieManager->setPublicCookie(
            AuthSession::LOGOUT_REASON_CODE_COOKIE_NAME,
            '',
            $metaData
        );

        return $this;
    }
}
