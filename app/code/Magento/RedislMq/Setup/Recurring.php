<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RedisMq\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;

/**
 * Class Recurring
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @param MessageQueueConfig $messageQueueConfig
     */
    public function __construct(MessageQueueConfig $messageQueueConfig)
    {
        $this->messageQueueConfig = $messageQueueConfig;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        foreach ($this->messageQueueConfig->getQueues() as $queue) {
            //@todo create queu in redis here ???
        }


    }
}
