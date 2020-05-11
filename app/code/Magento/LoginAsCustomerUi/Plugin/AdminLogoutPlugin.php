<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Plugin;

use Magento\Backend\Model\Auth;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\DeleteExpiredAuthenticationDataInterface;

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
     * @var DeleteExpiredAuthenticationDataInterface
     */
    private $deleteExpiredAuthenticationData;

    /**
     * @param ConfigInterface $config
     * @param DeleteExpiredAuthenticationDataInterface $deleteExpiredAuthenticationData
     */
    public function __construct(
        ConfigInterface $config,
        DeleteExpiredAuthenticationDataInterface $deleteExpiredAuthenticationData
    ) {
        $this->config = $config;
        $this->deleteExpiredAuthenticationData = $deleteExpiredAuthenticationData;
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
            $this->deleteExpiredAuthenticationData->execute($userId);
        }
    }
}
