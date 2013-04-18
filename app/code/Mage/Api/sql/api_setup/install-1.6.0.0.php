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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Api install
 *
 * @category    Mage
 * @package     Mage_Api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'api_assert'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('api_assert'))
    ->addColumn('assert_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Assert id')
    ->addColumn('assert_type', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Assert type')
    ->addColumn('assert_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Assert additional data')
    ->setComment('Api ACL Asserts');
$installer->getConnection()->createTable($table);

/**
 * Create table 'api_role'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('api_role'))
    ->addColumn('role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Role id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Parent role id')
    ->addColumn('tree_level', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Role level in tree')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort order to display on admin area')
    ->addColumn('role_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Role type')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'User id')
    ->addColumn('role_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Role name')
    ->addIndex($installer->getIdxName('api_role', array('parent_id', 'sort_order')),
        array('parent_id', 'sort_order'))
    ->addIndex($installer->getIdxName('api_role', array('tree_level')),
        array('tree_level'))
    ->setComment('Api ACL Roles');
$installer->getConnection()->createTable($table);

/**
 * Create table 'api_rule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('api_rule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Api rule Id')
    ->addColumn('role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Api role Id')
    ->addColumn('resource_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Module code')
    ->addColumn('api_privileges', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Privileges')
    ->addColumn('assert_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Assert id')
    ->addColumn('role_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
        ), 'Role type')
    ->addColumn('api_permission', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        ), 'Permission')
    ->addIndex($installer->getIdxName('api_rule', array('resource_id', 'role_id')),
        array('resource_id', 'role_id'))
    ->addIndex($installer->getIdxName('api_rule', array('role_id', 'resource_id')),
        array('role_id', 'resource_id'))
    ->addForeignKey($installer->getFkName('api_rule', 'role_id', 'api_role', 'role_id'),
        'role_id', $installer->getTable('api_role'), 'role_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Api ACL Rules');
$installer->getConnection()->createTable($table);

/**
 * Create table 'api_user'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('api_user'))
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'User id')
    ->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'First name')
    ->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Last name')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        ), 'Email')
    ->addColumn('username', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Nickname')
    ->addColumn('api_key', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Api key')
    ->addColumn('created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'User record create date')
    ->addColumn('modified', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'User record modify date')
    ->addColumn('lognum', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quantity of log ins')
    ->addColumn('reload_acl_flag', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Refresh ACL flag')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Account status')
    ->setComment('Api Users');
$installer->getConnection()->createTable($table);

/**
 * Create table 'api_session'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('api_session'))
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'User id')
    ->addColumn('logdate', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Login date')
    ->addColumn('sessid', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Sessioin id')
    ->addIndex($installer->getIdxName('api_session', array('user_id')),
        array('user_id'))
    ->addIndex($installer->getIdxName('api_session', array('sessid')),
        array('sessid'))
    ->addForeignKey($installer->getFkName('api_session', 'user_id', 'api_user', 'user_id'),
        'user_id', $installer->getTable('api_user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Api Sessions');
$installer->getConnection()->createTable($table);



$installer->endSetup();
