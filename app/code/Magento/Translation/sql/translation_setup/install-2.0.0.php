<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

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
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->setComment('Translations');
$installer->getConnection()->createTable($table);

$installer->endSetup();
