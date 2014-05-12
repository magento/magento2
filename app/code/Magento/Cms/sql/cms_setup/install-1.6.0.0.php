<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/* @var $installer \Magento\Framework\Module\Setup */
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
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Block ID'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Block Title'
)->addColumn(
    'identifier',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Block String Identifier'
)->addColumn(
    'content',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    array(),
    'Block Content'
)->addColumn(
    'creation_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Block Creation Time'
)->addColumn(
    'update_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Block Modification Time'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '1'),
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
    array('nullable' => false, 'primary' => true),
    'Block ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addIndex(
    $installer->getIdxName('cms_block_store', array('store_id')),
    array('store_id')
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
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Page ID'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true),
    'Page Title'
)->addColumn(
    'root_template',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true),
    'Page Template'
)->addColumn(
    'meta_keywords',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => true),
    'Page Meta Keywords'
)->addColumn(
    'meta_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => true),
    'Page Meta Description'
)->addColumn(
    'identifier',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    array('nullable' => true, 'default' => null),
    'Page String Identifier'
)->addColumn(
    'content_heading',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true),
    'Page Content Heading'
)->addColumn(
    'content',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    array(),
    'Page Content'
)->addColumn(
    'creation_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Page Creation Time'
)->addColumn(
    'update_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Page Modification Time'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '1'),
    'Is Page Active'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '0'),
    'Page Sort Order'
)->addColumn(
    'layout_update_xml',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => true),
    'Page Layout Update Content'
)->addColumn(
    'custom_theme',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    array('nullable' => true),
    'Page Custom Theme'
)->addColumn(
    'custom_root_template',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true),
    'Page Custom Template'
)->addColumn(
    'custom_layout_update_xml',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => true),
    'Page Custom Layout Update Content'
)->addColumn(
    'custom_theme_from',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    array('nullable' => true),
    'Page Custom Theme Active From Date'
)->addColumn(
    'custom_theme_to',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    array('nullable' => true),
    'Page Custom Theme Active To Date'
)->addIndex(
    $installer->getIdxName('cms_page', array('identifier')),
    array('identifier')
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
    array('nullable' => false, 'primary' => true),
    'Page ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addIndex(
    $installer->getIdxName('cms_page_store', array('store_id')),
    array('store_id')
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
