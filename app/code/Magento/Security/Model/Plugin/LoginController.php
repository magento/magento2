<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Backend\Controller\Adminhtml\Auth\Login;
use Magento\Security\Model\SecurityCookie;

/**
 * Magento\Backend\Controller\Adminhtml\Auth\Login decorator
 */
class LoginController
{
    /**
     * @param ManagerInterface $messageManager
     * @param AdminSessionsManager $sessionsManager
     * @param SecurityCookie $securityCookie
     */
    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly AdminSessionsManager $sessionsManager,
        private readonly SecurityCookie $securityCookie
    ) {
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
            $this->messageManager->addErrorMessage(
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
