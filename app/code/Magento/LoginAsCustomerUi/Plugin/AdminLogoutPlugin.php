<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Plugin;

use Magento\Backend\Model\Auth;
use Magento\LoginAsCustomer\Model\Config;
use Magento\LoginAsCustomerApi\Api\DeleteExpiredAuthenticationDataInterface;

class AdminLogoutPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DeleteExpiredAuthenticationDataInterface
     */
    private $deleteExpiredAuthenticationData;

    /**
     * @param Config $config
     * @param DeleteExpiredAuthenticationDataInterface $deleteExpiredAuthenticationData
     */
    public function __construct(
        Config $config,
        DeleteExpiredAuthenticationDataInterface $deleteExpiredAuthenticationData
    ) {
        $this->config = $config;
        $this->deleteExpiredAuthenticationData = $deleteExpiredAuthenticationData;
    }

    /**
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
