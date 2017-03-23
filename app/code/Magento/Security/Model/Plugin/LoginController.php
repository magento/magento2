<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    private $messageManager;

    /**
     * @var AdminSessionsManager
     */
    private $sessionsManager;

    /**
     * @var \Magento\Security\Model\SecurityCookie
     */
    private $securityCookie;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Model\SecurityCookie $securityCookie
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
     */
    private function isLoginForm(Login $login)
    {
        return $login->getRequest()->getUri() == $login->getUrl('*');
    }
}
