<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Framework\Module\Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'importexport_importdata'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('importexport_importdata'))
    ->addColumn(
        'id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Id'
    )
    ->addColumn(
        'entity',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        50,
        ['nullable' => false],
        'Entity'
    )
    ->addColumn(
        'behavior',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        10,
        ['nullable' => false, 'default' => 'append'],
        'Behavior'
    )
    ->addColumn(
        'data',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        '4G',
        ['default' => false],
        'Data'
    )
    ->setComment('Import Data Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
