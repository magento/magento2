<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'cms_block'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cms_block')
)->addColumn(
    'block_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Block ID'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Block Title'
)->addColumn(
    'identifier',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Block String Identifier'
)->addColumn(
    'content',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    [],
    'Block Content'
)->addColumn(
    'creation_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Block Creation Time'
)->addColumn(
    'update_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Block Modification Time'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '1'],
    'Is Block Active'
)->setComment(
    'CMS Block Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'cms_block_store'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cms_block_store')
)->addColumn(
    'block_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'primary' => true],
    'Block ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Store ID'
)->addIndex(
    $installer->getIdxName('cms_block_store', ['store_id']),
    ['store_id']
)->addForeignKey(
    $installer->getFkName('cms_block_store', 'block_id', 'cms_block', 'block_id'),
    'block_id',
    $installer->getTable('cms_block'),
    'block_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('cms_block_store', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'CMS Block To Store Linkage Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'cms_page'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cms_page')
)->addColumn(
    'page_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Page ID'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Page Title'
)->addColumn(
    'page_layout',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Page Layout'
)->addColumn(
    'meta_keywords',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    ['nullable' => true],
    'Page Meta Keywords'
)->addColumn(
    'meta_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    ['nullable' => true],
    'Page Meta Description'
)->addColumn(
    'identifier',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    ['nullable' => true, 'default' => null],
    'Page String Identifier'
)->addColumn(
    'content_heading',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Page Content Heading'
)->addColumn(
    'content',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    [],
    'Page Content'
)->addColumn(
    'creation_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Page Creation Time'
)->addColumn(
    'update_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Page Modification Time'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '1'],
    'Is Page Active'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '0'],
    'Page Sort Order'
)->addColumn(
    'layout_update_xml',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    ['nullable' => true],
    'Page Layout Update Content'
)->addColumn(
    'custom_theme',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    ['nullable' => true],
    'Page Custom Theme'
)->addColumn(
    'custom_root_template',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Page Custom Template'
)->addColumn(
    'custom_layout_update_xml',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    ['nullable' => true],
    'Page Custom Layout Update Content'
)->addColumn(
    'custom_theme_from',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    ['nullable' => true],
    'Page Custom Theme Active From Date'
)->addColumn(
    'custom_theme_to',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    ['nullable' => true],
    'Page Custom Theme Active To Date'
)->addIndex(
    $installer->getIdxName('cms_page', ['identifier']),
    ['identifier']
)->setComment(
    'CMS Page Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'cms_page_store'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cms_page_store')
)->addColumn(
    'page_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'primary' => true],
    'Page ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Store ID'
)->addIndex(
    $installer->getIdxName('cms_page_store', ['store_id']),
    ['store_id']
)->addForeignKey(
    $installer->getFkName('cms_page_store', 'page_id', 'cms_page', 'page_id'),
    'page_id',
    $installer->getTable('cms_page'),
    'page_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('cms_page_store', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'CMS Page To Store Linkage Table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
