<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Closure;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\SecurityCookie;
use Magento\Security\Model\UserExpirationManager;

/**
 * Magento\Backend\Model\Auth\Session decorator
 */
class AuthSession
{
    /**
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param SecurityCookie $securityCookie
     * @param UserExpirationManager|null $userExpirationManager
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ManagerInterface $messageManager,
        private readonly AdminSessionsManager $sessionsManager,
        protected readonly SecurityCookie $securityCookie,
        private ?UserExpirationManager $userExpirationManager = null
    ) {
        $this->userExpirationManager = $userExpirationManager ?:
            ObjectManager::getInstance()
                ->get(UserExpirationManager::class);
    }

    /**
     * Admin Session prolong functionality
     *
     * @param Session $session
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundProlong(Session $session, Closure $proceed)
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
