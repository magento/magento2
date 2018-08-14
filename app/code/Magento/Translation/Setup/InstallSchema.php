<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Setup;

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

        /**
         * Create table 'translation'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('translation'))
            ->addColumn(
                'key_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Key Id of Translation'
            )->addColumn(
                'string',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                    'default' => 'Translate String',
                ],
                'Translation String'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                ],
                'Store Id'
            )->addColumn(
                'translate',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Translate'
            )->addColumn(
                'locale',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                [
                    'nullable' => false,
                    'default'  => 'en_US',
                ],
                'Locale'
            )->addColumn(
                'crc_string',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                [
                    'nullable' => false,
                    'default'  => crc32('Translate String')
                ],
                'Translation String CRC32 Hash'
            )->addIndex(
                $installer->getIdxName(
                    'translation',
                    ['store_id', 'locale', 'crc_string', 'string'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['store_id', 'locale', 'crc_string', 'string'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $installer->getFkName('translation', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Translations');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
