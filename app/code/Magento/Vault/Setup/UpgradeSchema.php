<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Vault module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->upgradeTokenTableDefaultValues($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function upgradeTokenTableDefaultValues(SchemaSetupInterface $setup)
    {
        $columns = ['is_active', 'is_visible'];

        foreach ($columns as $columnName) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable(InstallSchema::PAYMENT_TOKEN_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => '1'
                ]
            );
        }
    }
}
