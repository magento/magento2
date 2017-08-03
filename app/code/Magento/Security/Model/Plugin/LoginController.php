<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Security\Model\AdminSessionsManager;
use Magento\Backend\Controller\Adminhtml\Auth\Login;

/**
 * Magento\Backend\Controller\Adminhtml\Auth\Login decorator
 * @since 2.1.0
 */
class LoginController
{
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
    private $securityCookie;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Model\SecurityCookie $securityCookie
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AdminSessionsManager $sessionsManager,
        \Magento\Security\Model\SecurityCookie $securityCookie
    ) {
        $this->messageManager = $messageManager;
        $this->sessionsManager = $sessionsManager;
        $this->securityCookie = $securityCookie;
    }

    /**
     * Before execute login
     *
     * @param Login $login
     * @return void
     * @since 2.1.0
     */
    public function beforeExecute(Login $login)
    {
        $logoutReasonCode = $this->securityCookie->getLogoutReasonCookie();
        if ($this->isLoginForm($login) && $logoutReasonCode >= 0) {
            $this->messageManager->addError(
                $this->sessionsManager->getLogoutReasonMessageByStatus($logoutReasonCode)
            );
            $this->securityCookie->deleteLogoutReasonCookie();
        }
    }

    /**
     * Check if the login form action is requested directly
     *
     * @param Login $login
     * @return bool
     * @since 2.1.0
     */
    private function isLoginForm(Login $login)
    {
        return $login->getRequest()->getUri() == $login->getUrl('*');
    }
}
