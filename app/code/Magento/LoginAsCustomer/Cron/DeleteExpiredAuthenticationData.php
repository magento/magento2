<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Cron;

use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\DeleteExpiredAuthenticationDataInterface;

/**
 * Delete expired authentication data cron task
 */
class DeleteExpiredAuthenticationData
{
    /**
     * @var DeleteExpiredAuthenticationDataInterface
     */
    private $deleteOldSecretsProcessor;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param DeleteExpiredAuthenticationDataInterface $deleteOldSecretsProcessor
     * @param ConfigInterface $config
     */
    public function __construct(
        DeleteExpiredAuthenticationDataInterface $deleteOldSecretsProcessor,
        ConfigInterface $config
    ) {
        $this->deleteOldSecretsProcessor = $deleteOldSecretsProcessor;
        $this->config = $config;
    }

    /**
     * Delete expired authentication data
     */
    public function execute(): void
    {
        if ($this->config->isEnabled()) {
            $this->deleteOldSecretsProcessor->execute();
        }
    }
}
