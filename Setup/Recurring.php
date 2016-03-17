<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Setup;

use Magento\Amqp\Model\Topology;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Recurring
 *
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var \Magento\Amqp\Model\Topology
     */
    protected $topology;

    /**
     * @param Topology $topology
     */
    public function __construct(Topology $topology)
    {
        $this->topology = $topology;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->topology->install();
    }
}
