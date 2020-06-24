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

/**
 * Delete all Login as Customer sessions for logging out admin.
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
     * @param ConfigInterface $config
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     */
    public function __construct(
        ConfigInterface $config,
        DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
    ) {
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
        if ($this->config->isEnabled()) {
            $userId = (int)$subject->getUser()->getId();
            $this->deleteAuthenticationDataForUser->execute($userId);
        }
    }
}
