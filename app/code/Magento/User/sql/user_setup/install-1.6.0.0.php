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
 * @category    Magento
 * @package     Magento_User
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer \Magento\Core\Model\Resource\Setup */

$installer->startSetup();

/**
 * Create table 'admin_assert'
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('admin_assert'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('admin_assert'))
        ->addColumn('assert_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Assert ID')
        ->addColumn('assert_type', \Magento\DB\Ddl\Table::TYPE_TEXT, 20, array(
            'nullable'  => true,
            'default'   => null,
            ), 'Assert Type')
        ->addColumn('assert_data', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
            ), 'Assert Data')
        ->setComment('Admin Assert Table');
    $installer->getConnection()->createTable($table);
}

/**
 * Create table 'admin_role'
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('admin_role'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('admin_role'))
        ->addColumn('role_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Role ID')
        ->addColumn('parent_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'Parent Role ID')
        ->addColumn('tree_level', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'Role Tree Level')
        ->addColumn('sort_order', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'Role Sort Order')
        ->addColumn('role_type', \Magento\DB\Ddl\Table::TYPE_TEXT, 1, array(
            'nullable'  => false,
            'default'   => '0',
            ), 'Role Type')
        ->addColumn('user_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'User ID')
        ->addColumn('role_name', \Magento\DB\Ddl\Table::TYPE_TEXT, 50, array(
            'nullable'  => true,
            'default'   => null,
            ), 'Role Name')
        ->addIndex($installer->getIdxName('admin_role', array('parent_id', 'sort_order')),
            array('parent_id', 'sort_order'))
        ->addIndex($installer->getIdxName('admin_role', array('tree_level')),
            array('tree_level'))
        ->setComment('Admin Role Table');
    $installer->getConnection()->createTable($table);
}
/**
 * Create table 'admin_rule'
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('admin_rule'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('admin_rule'))
        ->addColumn('rule_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'Rule ID')
        ->addColumn('role_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'Role ID')
        ->addColumn('resource_id', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
            'nullable'  => true,
            'default'   => null,
            ), 'Resource ID')
        ->addColumn('privileges', \Magento\DB\Ddl\Table::TYPE_TEXT, 20, array(
            'nullable'  => true,
            ), 'Privileges')
        ->addColumn('assert_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'Assert ID')
        ->addColumn('role_type', \Magento\DB\Ddl\Table::TYPE_TEXT, 1, array(
            ), 'Role Type')
        ->addColumn('permission', \Magento\DB\Ddl\Table::TYPE_TEXT, 10, array(
            ), 'Permission')
        ->addIndex($installer->getIdxName('admin_rule', array('resource_id', 'role_id')),
            array('resource_id', 'role_id'))
        ->addIndex($installer->getIdxName('admin_rule', array('role_id', 'resource_id')),
            array('role_id', 'resource_id'))
        ->addForeignKey($installer->getFkName('admin_rule', 'role_id', 'admin_role', 'role_id'),
            'role_id', $installer->getTable('admin_role'), 'role_id',
            \Magento\DB\Ddl\Table::ACTION_CASCADE, \Magento\DB\Ddl\Table::ACTION_CASCADE)
        ->setComment('Admin Rule Table');
    $installer->getConnection()->createTable($table);
}

/**
 * Create table 'admin_user'
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('admin_user'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('admin_user'))
        ->addColumn('user_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            ), 'User ID')
        ->addColumn('firstname', \Magento\DB\Ddl\Table::TYPE_TEXT, 32, array(
            'nullable'  => true,
            ), 'User First Name')
        ->addColumn('lastname', \Magento\DB\Ddl\Table::TYPE_TEXT, 32, array(
            'nullable'  => true,
            ), 'User Last Name')
        ->addColumn('email', \Magento\DB\Ddl\Table::TYPE_TEXT, 128, array(
            'nullable'  => true,
            ), 'User Email')
        ->addColumn('username', \Magento\DB\Ddl\Table::TYPE_TEXT, 40, array(
            'nullable'  => true,
            ), 'User Login')
        ->addColumn('password', \Magento\DB\Ddl\Table::TYPE_TEXT, 40, array(
            'nullable'  => true,
            ), 'User Password')
        ->addColumn('created', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            'default'   => \Magento\DB\Ddl\Table::TIMESTAMP_INIT,
        ), 'User Created Time')
        ->addColumn('modified', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            ), 'User Modified Time')
        ->addColumn('logdate', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            ), 'User Last Login Time')
        ->addColumn('lognum', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            ), 'User Login Number')
        ->addColumn('reload_acl_flag', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
            'default'   => '0',
            ), 'Reload ACL')
        ->addColumn('is_active', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
            'default'   => '1',
            ), 'User Is Active')
        ->addColumn('extra', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
            ), 'User Extra Data')
        ->addIndex(
            $installer->getIdxName(
                'admin_user',
                array('username'),
                \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            array('username'),
            array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        )
        ->setComment('Admin User Table');
    $installer->getConnection()->createTable($table);
}
$installer->endSetup();
