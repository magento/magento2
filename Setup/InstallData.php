<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\Config\Converter as AmqpConfigConverter;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * Initialize dependencies.
     *
     * @param AmqpConfig $amqpConfig
     */
    public function __construct(AmqpConfig $amqpConfig)
    {
        $this->amqpConfig = $amqpConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $binds = $this->amqpConfig->get()[AmqpConfigConverter::BINDS];
        $queues = [];
        foreach ($binds as $bind) {
            $queues[] = $bind[AmqpConfigConverter::BIND_QUEUE];
        }
        $queues = array_unique($queues);
        /** Populate 'queue' table */
        foreach ($queues as $queueName) {
            $setup->getConnection()->insert($setup->getTable('queue'), ['name' => $queueName]);
        }

        $setup->endSetup();
    }
}
