<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Stomp\TopologyInstaller;

/**
 * Class Recurring used for installing queues in ActiveMq
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var TopologyInstaller
     */
    protected TopologyInstaller $topologyInstaller;

    /**
     * @param TopologyInstaller $topologyInstaller
     */
    public function __construct(TopologyInstaller $topologyInstaller)
    {
        $this->topologyInstaller = $topologyInstaller;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->topologyInstaller->install();
    }
}
