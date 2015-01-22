<?php
/**
 * GoogleOptimizer install
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$installer = $this;
/* @var $installer \Magento\Framework\Module\Setup */

$installer->startSetup();

/**
 * Create table 'googleoptimizer_code'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('googleoptimizer_code'))
    ->addColumn(
        'code_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Google experiment code id'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Optimized entity id product id or catalog id'
    )
    ->addColumn(
        'entity_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        50,
        [],
        'Optimized entity type'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Store id'
    )
    ->addColumn(
        'experiment_script',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        '64k',
        [],
        'Google experiment script'
    )
    ->addIndex(
        $installer->getIdxName(
            'googleoptimizer_code',
            ['store_id', 'entity_id', 'entity_type'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['store_id', 'entity_id', 'entity_type'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName('googleoptimizer_code', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Google Experiment code');
$installer->getConnection()->createTable($table);

$installer->endSetup();
