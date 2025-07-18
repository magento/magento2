<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\Amqp\TopologyInstaller;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Recurring implements InstallSchemaInterface
{
    /**
     * @var TopologyInstaller
     */
    protected $topologyInstaller;

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
