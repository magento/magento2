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
use Magento\Framework\DB\Ddl\Table;

/* @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Create table 'store_website'
 */
$table = $connection->newTable(
    $installer->getTable('store_website')
)->addColumn(
    'website_id',
    Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website Id'
)->addColumn(
    'code',
    Table::TYPE_TEXT,
    32,
    array(),
    'Code'
)->addColumn(
    'name',
    Table::TYPE_TEXT,
    64,
    array(),
    'Website Name'
)->addColumn(
    'sort_order',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Sort Order'
)->addColumn(
    'default_group_id',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Default Group Id'
)->addColumn(
    'is_default',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Defines Is Website Default'
)->addIndex(
    $installer->getIdxName(
        'store_website',
        array('code'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('code'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('store_website', array('sort_order')),
    array('sort_order')
)->addIndex(
    $installer->getIdxName('store_website', array('default_group_id')),
    array('default_group_id')
)->setComment(
    'Websites'
);
$connection->createTable($table);

/**
 * Create table 'store_group'
 */
$table = $connection->newTable(
    $installer->getTable('store_group')
)->addColumn(
    'group_id',
    Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Group Id'
)->addColumn(
    'website_id',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Website Id'
)->addColumn(
    'name',
    Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Store Group Name'
)->addColumn(
    'root_category_id',
    Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Root Category Id'
)->addColumn(
    'default_store_id',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Default Store Id'
)->addIndex(
    $installer->getIdxName('store_group', array('website_id')),
    array('website_id')
)->addIndex(
    $installer->getIdxName('store_group', array('default_store_id')),
    array('default_store_id')
)->addForeignKey(
    $installer->getFkName('store_group', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    Table::ACTION_CASCADE,
    Table::ACTION_CASCADE
)->setComment(
    'Store Groups'
);
$connection->createTable($table);

/**
 * Create table 'store'
 */
$table = $connection->newTable(
    $installer->getTable('store')
)->addColumn(
    'store_id',
    Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store Id'
)->addColumn(
    'code',
    Table::TYPE_TEXT,
    32,
    array(),
    'Code'
)->addColumn(
    'website_id',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Website Id'
)->addColumn(
    'group_id',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Group Id'
)->addColumn(
    'name',
    Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Store Name'
)->addColumn(
    'sort_order',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store Sort Order'
)->addColumn(
    'is_active',
    Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store Activity'
)->addIndex(
    $installer->getIdxName('store', array('code'), \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
    array('code'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('store', array('website_id')),
    array('website_id')
)->addIndex(
    $installer->getIdxName('store', array('is_active', 'sort_order')),
    array('is_active', 'sort_order')
)->addIndex(
    $installer->getIdxName('store', array('group_id')),
    array('group_id')
)->addForeignKey(
    $installer->getFkName('store', 'group_id', 'store_group', 'group_id'),
    'group_id',
    $installer->getTable('store_group'),
    'group_id',
    Table::ACTION_CASCADE,
    Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('store', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    Table::ACTION_CASCADE,
    Table::ACTION_CASCADE
)->setComment(
    'Stores'
);
$connection->createTable($table);

/**
 * Insert websites
 */
$connection->insertForce(
    $installer->getTable('store_website'),
    array(
        'website_id' => 0,
        'code' => 'admin',
        'name' => 'Admin',
        'sort_order' => 0,
        'default_group_id' => 0,
        'is_default' => 0
    )
);
$connection->insertForce(
    $installer->getTable('store_website'),
    array(
        'website_id' => 1,
        'code' => 'base',
        'name' => 'Main Website',
        'sort_order' => 0,
        'default_group_id' => 1,
        'is_default' => 1
    )
);

/**
 * Insert store groups
 */
$connection->insertForce(
    $installer->getTable('store_group'),
    array('group_id' => 0, 'website_id' => 0, 'name' => 'Default', 'root_category_id' => 0, 'default_store_id' => 0)
);
$connection->insertForce(
    $installer->getTable('store_group'),
    array(
        'group_id' => 1,
        'website_id' => 1,
        'name' => 'Main Website Store',
        'root_category_id' => 2,
        'default_store_id' => 1
    )
);

/**
 * Insert stores
 */
$connection->insertForce(
    $installer->getTable('store'),
    array(
        'store_id' => 0,
        'code' => 'admin',
        'website_id' => 0,
        'group_id' => 0,
        'name' => 'Admin',
        'sort_order' => 0,
        'is_active' => 1
    )
);
$connection->insertForce(
    $installer->getTable('store'),
    array(
        'store_id' => 1,
        'code' => 'default',
        'website_id' => 1,
        'group_id' => 1,
        'name' => 'Default Store View',
        'sort_order' => 0,
        'is_active' => 1
    )
);

$installer->endSetup();
