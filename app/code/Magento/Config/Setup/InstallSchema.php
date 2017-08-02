<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $setup->startSetup();

        /**
         * Create table 'core_config_data'
         */
        $table = $connection->newTable(
            $setup->getTable('core_config_data')
        )->addColumn(
            'config_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Config Id'
        )->addColumn(
            'scope',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            8,
            ['nullable' => false, 'default' => 'default'],
            'Config Scope'
        )->addColumn(
            'scope_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Config Scope Id'
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'general'],
            'Config Path'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Config Value'
        )->addIndex(
            $setup->getIdxName(
                'core_config_data',
                ['scope', 'scope_id', 'path'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['scope', 'scope_id', 'path'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Config Data'
        );
        $connection->createTable($table);

        $setup->endSetup();
    }
}
