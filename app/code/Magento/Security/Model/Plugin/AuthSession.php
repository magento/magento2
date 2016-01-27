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
     * @var \Magento\Security\Helper\SecurityCookie
     */
    protected $securityCookieHelper;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Helper\SecurityCookie $securityCookieHelper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Security\Helper\SecurityCookie $securityCookieHelper
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->securityCookieHelper = $securityCookieHelper;
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
     * Add user logout notification
     *
     * @return $this
     */
    protected function addUserLogoutNotification()
    {
        if ($this->isAjaxRequest()) {
            $this->securityCookieHelper->setLogoutReasonCookie(
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
     * Check if a request is session check
     *
     * @return bool
     */
    protected function isSessionCheckRequest()
    {
        return $this->request->getModuleName() == 'security' && $this->request->getActionName() == 'check';
    }

    /**
     * Check if a request is AJAX request
     *
     * @return bool
     */
    protected function isAjaxRequest()
    {
        return (bool) $this->request->getParam('isAjax');
    }
}
