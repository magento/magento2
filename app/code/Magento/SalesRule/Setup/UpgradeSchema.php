<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the SalesRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addDefaultValueForDiscountStep($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add default value of 0 for the discount step column
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addDefaultValueForDiscountStep(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->modifyColumn(
            $setup->getTable('salesrule'),
            'discount_step',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Discount Step',
            ]
        );
    }
}
