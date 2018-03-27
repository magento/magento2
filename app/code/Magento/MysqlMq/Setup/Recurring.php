<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;

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
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $binds = $this->messageQueueConfig->getBinds();
        $queues = [];
        foreach ($binds as $bind) {
            $queues[] = $bind[MessageQueueConfig::BIND_QUEUE];
        }
        $connection = $setup->getConnection();
        $existingQueues = $connection->fetchCol($connection->select()->from($setup->getTable('queue'), 'name'));
        $queues = array_unique(array_diff($queues, $existingQueues));
        /** Populate 'queue' table */
        if (!empty($queues)) {
            $connection->insertArray($setup->getTable('queue'), ['name'], $queues);
        }

        $setup->endSetup();
    }
}
