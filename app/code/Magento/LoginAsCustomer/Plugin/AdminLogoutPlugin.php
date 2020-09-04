<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Plugin;

use Magento\Backend\Model\Auth;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerCustomerIdInterface;

/**
 * Delete all Login as Customer sessions for logging out admin.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AdminLogoutPlugin
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DeleteAuthenticationDataForUserInterface
     */
    private $deleteAuthenticationDataForUser;

    /**
     * @var GetLoggedAsCustomerCustomerIdInterface
     */
    private $getLoggedAsCustomerCustomerId;

    /**
     * @param ConfigInterface $config
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     * @param GetLoggedAsCustomerCustomerIdInterface $getLoggedAsCustomerCustomerId
     */
    public function __construct(
        ConfigInterface $config,
        DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser,
        GetLoggedAsCustomerCustomerIdInterface $getLoggedAsCustomerCustomerId
    ) {
        $this->config = $config;
        $this->deleteAuthenticationDataForUser = $deleteAuthenticationDataForUser;
        $this->getLoggedAsCustomerCustomerId = $getLoggedAsCustomerCustomerId;
    }

    /**
     * Delete all Login as Customer sessions for logging out admin.
     *
     * @param Auth $subject
     */
    public function beforeLogout(Auth $subject): void
    {
        $user = $subject->getUser();
        $isLoggedAsCustomer = (bool)$this->getLoggedAsCustomerCustomerId->execute();
        if ($this->config->isEnabled() && $user && $isLoggedAsCustomer) {
            $userId = (int)$user->getId();
            $this->deleteAuthenticationDataForUser->execute($userId);
        }
    }
}
