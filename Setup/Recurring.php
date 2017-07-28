<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\Amqp\TopologyInstaller;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Recurring
 * @since 2.1.0
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var TopologyInstaller
     * @since 2.2.0
     */
    protected $topologyInstaller;

    /**
     * @param TopologyInstaller $topologyInstaller
     * @since 2.1.0
     */
    public function __construct(TopologyInstaller $topologyInstaller)
    {
        $this->topologyInstaller = $topologyInstaller;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->topologyInstaller->install();
    }
}
