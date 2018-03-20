<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the AsynchronousOperations module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addResultSerializedDataColumn($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add the column 'result_serialized_data' to the Bulk Operation table.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addResultSerializedDataColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('magento_operation');
        if (!$connection->tableColumnExists($tableName, 'result_serialized_data')) {
            $connection->addColumn(
                $tableName,
                'result_serialized_data',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                    'size'     => 0,
                    'nullable' => true,
                    'comment'  => 'Result data (serialized) after perform an operation',
                ]
            );
        }
    }
}
