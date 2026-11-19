<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\MysqlMq\Setup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\MysqlMq\Model\Queue\Synchronizer;

class Recurring implements InstallSchemaInterface
{
    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @param MessageQueueConfig $messageQueueConfig
     * @param Synchronizer|null $synchronizer
     */
    public function __construct(
        MessageQueueConfig $messageQueueConfig,
        ?Synchronizer $synchronizer = null
    ) {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->synchronizer = $synchronizer ?? ObjectManager::getInstance()->get(Synchronizer::class);
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->synchronizer->synchronize();
    }
}
