<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Magento\Backend\Model\Auth\Session decorator
 * @since 2.1.0
 */
class AuthSession
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.1.0
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.1.0
     */
    private $messageManager;

    /**
     * @var AdminSessionsManager
     * @since 2.1.0
     */
    private $sessionsManager;

    /**
     * @var \Magento\Security\Model\SecurityCookie
     * @since 2.1.0
     */
    protected $securityCookie;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Model\SecurityCookie $securityCookie
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Security\Model\SecurityCookie $securityCookie
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->securityCookie = $securityCookie;
    }

    /**
     * Admin Session prolong functionality
     *
     * @param Session $session
     * @param \Closure $proceed
     * @return mixed
     * @since 2.1.0
     */
    public function aroundProlong(Session $session, \Closure $proceed)
    {
        if (!$this->sessionsManager->getCurrentSession()->isLoggedInStatus()) {
            $session->destroy();
            $this->addUserLogoutNotification();
            return null;
        }
        $result = $proceed();
        $this->sessionsManager->processProlong();
        return $result;
    }

    /**
     * Add user logout notification
     *
     * @return $this
     * @since 2.1.0
     */
    private function addUserLogoutNotification()
    {
        if ($this->isAjaxRequest()) {
            $this->securityCookie->setLogoutReasonCookie(
                $this->sessionsManager->getCurrentSession()->getStatus()
            );
        } elseif ($message = $this->sessionsManager->getLogoutReasonMessage()) {
            $this->messageManager->addError($message);
        }

        return $this;
    }

    /**
     * Check if a request is AJAX request
     *
     * @return bool
     * @since 2.1.0
     */
    private function isAjaxRequest()
    {
        return (bool) $this->request->getParam('isAjax');
    }
}
