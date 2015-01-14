<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'sitemap'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('sitemap')
)->addColumn(
    'sitemap_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Sitemap Id'
)->addColumn(
    'sitemap_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Sitemap Type'
)->addColumn(
    'sitemap_filename',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Sitemap Filename'
)->addColumn(
    'sitemap_path',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sitemap Path'
)->addColumn(
    'sitemap_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Sitemap Time'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Store id'
)->addIndex(
    $installer->getIdxName('sitemap', ['store_id']),
    ['store_id']
)->addForeignKey(
    $installer->getFkName('sitemap', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'XML Sitemap'
);

$installer->getConnection()->createTable($table);

$installer->endSetup();
