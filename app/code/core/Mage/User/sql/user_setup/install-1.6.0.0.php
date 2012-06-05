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
 * @category    Mage
 * @package     Mage_User
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'admin_assert'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('admin_assert'))
    ->addColumn('assert_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Assert ID')
    ->addColumn('assert_type', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Assert Type')
    ->addColumn('assert_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Assert Data')
    ->setComment('Admin Assert Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'admin_role'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('admin_role'))
    ->addColumn('role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Role ID')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Parent Role ID')
    ->addColumn('tree_level', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Role Tree Level')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Role Sort Order')
    ->addColumn('role_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Role Type')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'User ID')
    ->addColumn('role_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Role Name')
    ->addIndex($installer->getIdxName('admin_role', array('parent_id', 'sort_order')),
        array('parent_id', 'sort_order'))
    ->addIndex($installer->getIdxName('admin_role', array('tree_level')),
        array('tree_level'))
    ->setComment('Admin Role Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'admin_rule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('admin_rule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule ID')
    ->addColumn('role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Role ID')
    ->addColumn('resource_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Resource ID')
    ->addColumn('privileges', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable'  => true,
        ), 'Privileges')
    ->addColumn('assert_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Assert ID')
    ->addColumn('role_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
        ), 'Role Type')
    ->addColumn('permission', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        ), 'Permission')
    ->addIndex($installer->getIdxName('admin_rule', array('resource_id', 'role_id')),
        array('resource_id', 'role_id'))
    ->addIndex($installer->getIdxName('admin_rule', array('role_id', 'resource_id')),
        array('role_id', 'resource_id'))
    ->addForeignKey($installer->getFkName('admin_rule', 'role_id', 'admin_role', 'role_id'),
        'role_id', $installer->getTable('admin_role'), 'role_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Admin Rule Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'admin_user'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('admin_user'))
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'User ID')
    ->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        ), 'User First Name')
    ->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        ), 'User Last Name')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => true,
        ), 'User Email')
    ->addColumn('username', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        'nullable'  => true,
        ), 'User Login')
    ->addColumn('password', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        'nullable'  => true,
        ), 'User Password')
    ->addColumn('created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'User Created Time')
    ->addColumn('modified', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'User Modified Time')
    ->addColumn('logdate', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'User Last Login Time')
    ->addColumn('lognum', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'User Login Number')
    ->addColumn('reload_acl_flag', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Reload ACL')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'User Is Active')
    ->addColumn('extra', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'User Extra Data')
    ->addIndex($installer->getIdxName('admin_user', array('username'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('username'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Admin User Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
