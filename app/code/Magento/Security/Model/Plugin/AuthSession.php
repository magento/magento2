<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\UserExpirationManager;

/**
 * Magento\Backend\Model\Auth\Session decorator
 */
class AuthSession
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var AdminSessionsManager
     */
    private $sessionsManager;

    /**
     * @var \Magento\Security\Model\SecurityCookie
     */
    protected $securityCookie;

    /**
     * @var UserExpirationManager
     */
    private $userExpirationManager;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Model\SecurityCookie $securityCookie
     * @param UserExpirationManager|null $userExpirationManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Security\Model\SecurityCookie $securityCookie,
        \Magento\Security\Model\UserExpirationManager $userExpirationManager = null
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->securityCookie = $securityCookie;
        $this->userExpirationManager = $userExpirationManager ?:
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Security\Model\UserExpirationManager::class);
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
        if (!$this->sessionsManager->getCurrentSession()->isLoggedInStatus()) {
            $session->destroy();
            $this->addUserLogoutNotification();
            return null;
        } elseif ($this->userExpirationManager->isUserExpired($session->getUser()->getId())) {
            $this->userExpirationManager->deactivateExpiredUsersById([$session->getUser()->getId()]);
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
     */
    private function addUserLogoutNotification()
    {
        if ($this->isAjaxRequest()) {
            $this->securityCookie->setLogoutReasonCookie(
                $this->sessionsManager->getCurrentSession()->getStatus()
            );
        } elseif ($message = $this->sessionsManager->getLogoutReasonMessage()) {
            $this->messageManager->addErrorMessage($message);
        }

        return $this;
    }

    /**
     * Check if a request is AJAX request
     *
     * @return bool
     */
    private function isAjaxRequest()
    {
        return (bool) $this->request->getParam('isAjax');
    }
}
