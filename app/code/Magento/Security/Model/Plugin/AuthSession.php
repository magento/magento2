<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

/**
 * Magento\Backend\Model\Auth\Session decorator
 */
class AuthSession
{
    /**
     * Cookie name
     */
    const LOGOUT_REASON_CODE_COOKIE_NAME = 'loggedOutReasonCode';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

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
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager
     * @param CookieReaderInterface $cookieReader
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager,
        CookieReaderInterface $cookieReader,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->phpCookieManager = $phpCookieManager;
        $this->cookieReader = $cookieReader;
        $this->backendData = $backendData;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Admin Session prolong functionality
     *
     * @param Session $session
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundProlong(Session $session, \Closure $proceed)
    {
        if (!$this->isSessionCheckRequest()) {
            if (!$this->sessionsManager->getCurrentSession()->isActive()) {
                $session->destroy();
                $this->addUserLogoutNotification();
                return null;
            }
            $result = $proceed();
            $this->sessionsManager->processProlong();
            return $result;
        }
    }

    /**
     * @return $this
     */
    protected function addUserLogoutNotification()
    {
        if ($this->isAjaxRequest()) {
            $this->setLogoutReasonCookie(
                $this->sessionsManager->getCurrentSession()->getStatus()
            );
        } else {
            $this->messageManager->addError(
                $this->sessionsManager->getLogoutReasonMessage()
            );
        }

        return $this;
    }

    /**
     * @param int $status
     * @return $this
     */
    protected function setLogoutReasonCookie($status)
    {
        /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $metaData */
        $metaData = $this->cookieMetadataFactory->create();
        $metaData->setPath('/' . $this->backendData->getAreaFrontName());

        $this->phpCookieManager->setPublicCookie(
            self::LOGOUT_REASON_CODE_COOKIE_NAME,
            (int) $status,
            $metaData
        );

        return $this;
    }

    /**
     * @return bool
     */
    protected function isSessionCheckRequest()
    {
        return $this->request->getModuleName() == 'security' && $this->request->getActionName() == 'check';
    }

    /**
     * @return bool
     */
    protected function isAjaxRequest()
    {
        return (bool) $this->request->getParam('isAjax');
    }
}
