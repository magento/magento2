<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DesignEditor\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        /**
         * Create table 'vde_theme_change'
         */
        $table = $connection->newTable(
            $setup->getTable('vde_theme_change')
        )->addColumn(
            'change_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Theme Change Identifier'
        )->addColumn(
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Theme Id'
        )->addColumn(
            'change_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Change Time'
        )->addForeignKey(
            $setup->getFkName('vde_theme_change', 'theme_id', 'theme', 'theme_id'),
            'theme_id',
            $setup->getTable('theme'),
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Design Editor Theme Change'
        );

        $connection->createTable($table);

        $setup->endSetup();

    }
}
