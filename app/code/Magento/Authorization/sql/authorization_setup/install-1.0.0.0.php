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

if ($installer->getConnection()->isTableExists($installer->getTable('admin_role'))) {
    /**
     * Rename existing 'admin_role' table into 'authorization_role' (to avoid forcing Magento re-installation)
     * TODO: This conditional logic can be removed some time after pull request is delivered to the mainline
     */
    $installer->getConnection()->renameTable(
        $installer->getTable('admin_role'),
        $installer->getTable('authorization_role')
    );

} else if (!$installer->getConnection()->isTableExists($installer->getTable('authorization_role'))) {
    /**
     * Create table 'authorization_role'
     */
    $table = $installer->getConnection()->newTable(
        $installer->getTable('authorization_role')
    )->addColumn(
        'role_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
        'Role ID'
    )->addColumn(
        'parent_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('unsigned' => true, 'nullable' => false, 'default' => '0'),
        'Parent Role ID'
    )->addColumn(
        'tree_level',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array('unsigned' => true, 'nullable' => false, 'default' => '0'),
        'Role Tree Level'
    )->addColumn(
        'sort_order',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array('unsigned' => true, 'nullable' => false, 'default' => '0'),
        'Role Sort Order'
    )->addColumn(
        'role_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        1,
        array('nullable' => false, 'default' => '0'),
        'Role Type'
    )->addColumn(
        'user_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('unsigned' => true, 'nullable' => false, 'default' => '0'),
        'User ID'
    )->addColumn(
        'user_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        16,
        array('nullable' => true, 'default' => null),
        'User Type'
    )->addColumn(
        'role_name',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        50,
        array('nullable' => true, 'default' => null),
        'Role Name'
    )->addIndex(
        $installer->getIdxName('authorization_role', array('parent_id', 'sort_order')),
        array('parent_id', 'sort_order')
    )->addIndex(
        $installer->getIdxName('authorization_role', array('tree_level')),
        array('tree_level')
    )->setComment(
        'Admin Role Table'
    );
    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('admin_rule'))) {
    /**
     * Rename existing 'admin_rule' table into 'authorization_rule' (to avoid forcing Magento re-installation)
     * TODO: This conditional logic can be removed some time after pull request is delivered to the mainline
     */
    $installer->getConnection()->renameTable(
        $installer->getTable('admin_rule'),
        $installer->getTable('authorization_rule')
    );

} else if (!$installer->getConnection()->isTableExists($installer->getTable('authorization_rule'))) {
    /**
     * Create table 'authorization_rule'
     */
    $table = $installer->getConnection()->newTable(
        $installer->getTable('authorization_rule')
    )->addColumn(
        'rule_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
        'Rule ID'
    )->addColumn(
        'role_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('unsigned' => true, 'nullable' => false, 'default' => '0'),
        'Role ID'
    )->addColumn(
        'resource_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        array('nullable' => true, 'default' => null),
        'Resource ID'
    )->addColumn(
        'privileges',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        20,
        array('nullable' => true),
        'Privileges'
    )->addColumn(
        'permission',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        10,
        array(),
        'Permission'
    )->addIndex(
        $installer->getIdxName('authorization_rule', array('resource_id', 'role_id')),
        array('resource_id', 'role_id')
    )->addIndex(
        $installer->getIdxName('authorization_rule', array('role_id', 'resource_id')),
        array('role_id', 'resource_id')
    )->addForeignKey(
        $installer->getFkName('authorization_rule', 'role_id', 'authorization_role', 'role_id'),
        'role_id',
        $installer->getTable('authorization_role'),
        'role_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->setComment(
        'Admin Rule Table'
    );
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
