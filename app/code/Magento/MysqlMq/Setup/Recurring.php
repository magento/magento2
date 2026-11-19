<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\MysqlMq\Model\Queue\QueueConfigSynchronizer;

/**
 * Class Recurring
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @param QueueConfigSynchronizer $queueConfigSynchronizer
     */
    public function __construct(
        private readonly QueueConfigSynchronizer $queueConfigSynchronizer
    ) {
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $queues = $this->queueConfigSynchronizer->getMissingNames();
        /** Populate 'queue' table */
        if (!empty($queues)) {
            $connection = $setup->getConnection();
            $connection->insertArray($setup->getTable('queue'), ['name'], $queues);
        }

        $setup->endSetup();
    }
}
