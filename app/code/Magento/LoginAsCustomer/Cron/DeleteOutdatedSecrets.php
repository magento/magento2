<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Cron;

use Magento\LoginAsCustomer\Api\ConfigInterface;
use Magento\LoginAsCustomer\Api\DeleteOutdatedSecretsInterface;

/**
 * @api
 */
class DeleteOutdatedSecrets implements DeleteOutdatedSecretsInterface
{
    /**
     * @var DeleteOutdatedSecretsInterface
     */
    private $deleteOldSecretsProcessor;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param DeleteOutdatedSecretsInterface $deleteOldSecretsProcessor
     * @param ConfigInterface $config
     */
    public function __construct(
        DeleteOutdatedSecretsInterface $deleteOldSecretsProcessor,
        ConfigInterface $config
    ) {
        $this->deleteOldSecretsProcessor = $deleteOldSecretsProcessor;
        $this->config = $config;
    }

    /**
     * Delete old secret key records
     */
    public function execute():void
    {
        if ($this->config->isEnabled()) {
            $this->deleteOldSecretsProcessor->execute();
        }
    }
}
