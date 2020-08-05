<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Plugin;

use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;

/**
 * Delete all Login as Customer sessions for logging out admin.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AdminLogoutPlugin
{
    /**
     * @var AuthSession
     */
    private $authSession;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DeleteAuthenticationDataForUserInterface
     */
    private $deleteAuthenticationDataForUser;

    /**
     * @param AuthSession $authSession
     * @param ConfigInterface $config
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     */
    public function __construct(
        AuthSession $authSession,
        ConfigInterface $config,
        DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
    ) {
        $this->authSession = $authSession;
        $this->config = $config;
        $this->deleteAuthenticationDataForUser = $deleteAuthenticationDataForUser;
    }

    /**
     * Delete all Login as Customer sessions for logging out admin.
     *
     * @param Auth $subject
     */
    public function beforeLogout(Auth $subject): void
    {
        $user = $subject->getUser();
        $isLoggedAsCustomer = $this->authSession->getIsLoggedAsCustomer();
        if ($this->config->isEnabled() && $user && $isLoggedAsCustomer) {
            $userId = (int)$user->getId();
            $this->deleteAuthenticationDataForUser->execute($userId);
        }
    }
}
