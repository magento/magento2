<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Cron;

use Magento\LoginAsCustomer\Api\DeleteOldSecretsInterface;
use Magento\LoginAsCustomer\Model\Config;

/**
 * @api
 */
class DeleteOldSecrets implements DeleteOldSecretsInterface
{
    /**
     * @var DeleteOldSecretsInterface
     */
    private $deleteOldSecretsProcessor;

    /**
     * @var Config
     */
    private $config;

    /**
     * DeleteOldSecrets constructor.
     * @param DeleteOldSecretsInterface $deleteOldSecretsProcessor
     * @param Config $config
     */
    public function __construct(
        DeleteOldSecretsInterface $deleteOldSecretsProcessor,
        Config $config
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
