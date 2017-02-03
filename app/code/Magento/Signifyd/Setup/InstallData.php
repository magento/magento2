<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }
    
    /**
     * Installs data for sales module
     *
     * Update of sales_order_grid* tables is provided here to be sure that these tables are already created. {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->resource->getConnection('sales_order')->addColumn(
            $setup->getTable('sales_order_grid'),
            'signifyd_guarantee_status',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Signifyd Guarantee Disposition Status'
            ]
        );

        $this->resource->getConnection('sales_order')->addColumn(
            $setup->getTable('magento_sales_order_grid_archive'),
            'signifyd_guarantee_status',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Signifyd Guarantee Disposition Status'
            ]
        );
    }
}
