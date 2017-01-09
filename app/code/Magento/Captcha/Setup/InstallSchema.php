<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Setup;

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
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('captcha_log')
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false, 'primary' => true],
            'Type'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Value'
        )->addColumn(
            'count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Count'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Update Time'
        )->setComment(
            'Count Login Attempts'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
