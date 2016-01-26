<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Security\Model\AdminSessionsManager;
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
     * @var \Magento\Security\Helper\SecurityCookie
     */
    protected $securityCookieHelper;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Helper\SecurityCookie $securityCookieHelper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Security\Helper\SecurityCookie $securityCookieHelper
    ) {
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->securityCookieHelper = $securityCookieHelper;
    }

    /**
     * Before execute login
     *
     * @param Login $login
     * @return void
     */
    public function beforeExecute(Login $login)
    {
        $logoutReasonCode = $this->securityCookieHelper->getLogoutReasonCookie();
        if ($this->isLoginForm($login) && $logoutReasonCode >= 0) {
            $this->messageManager->addError(
                $this->sessionsManager->getLogoutReasonMessageByStatus($logoutReasonCode)
            );
            $this->securityCookieHelper->deleteLogoutReasonCookie();
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
}
