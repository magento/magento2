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
 * @package     Mage_Poll
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/**
 * Create table 'poll'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('poll'))
    ->addColumn('poll_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Poll Id')
    ->addColumn('poll_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Poll title')
    ->addColumn('votes_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Votes Count')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store id')
    ->addColumn('date_posted', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Date posted')
    ->addColumn('date_closed', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Date closed')
    ->addColumn('active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Is active')
    ->addColumn('closed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is closed')
    ->addColumn('answers_display', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        ), 'Answers display')
    ->addIndex($installer->getIdxName('poll', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('poll', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Poll');
$installer->getConnection()->createTable($table);

/**
 * Create table 'poll_answer'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('poll_answer'))
    ->addColumn('answer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Answer Id')
    ->addColumn('poll_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Poll Id')
    ->addColumn('answer_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Answer title')
    ->addColumn('votes_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Votes Count')
    ->addColumn('answer_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Answers display')
    ->addIndex($installer->getIdxName('poll_answer', array('poll_id')),
        array('poll_id'))
    ->addForeignKey($installer->getFkName('poll_answer', 'poll_id', 'poll', 'poll_id'),
        'poll_id', $installer->getTable('poll'), 'poll_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Poll Answers');
$installer->getConnection()->createTable($table);

/**
 * Create table 'poll_store'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('poll_store'))
    ->addColumn('poll_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'primary'   => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Poll Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'primary'   => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store id')
    ->addIndex($installer->getIdxName('poll_store', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('poll_store', 'poll_id', 'poll', 'poll_id'),
        'poll_id', $installer->getTable('poll'), 'poll_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('poll_store', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Poll Store');
$installer->getConnection()->createTable($table);

/**
 * Create table 'poll_vote'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('poll_vote'))
    ->addColumn('vote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Vote Id')
    ->addColumn('poll_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Poll Id')
    ->addColumn('poll_answer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Poll answer id')
    ->addColumn('ip_address', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'nullable'  => true,
        ), 'Poll answer id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        ), 'Customer id')
    ->addColumn('vote_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Date closed')
    ->addIndex($installer->getIdxName('poll_vote', array('poll_answer_id')),
        array('poll_answer_id'))
    ->addForeignKey($installer->getFkName('poll_vote', 'poll_answer_id', 'poll_answer', 'answer_id'),
        'poll_answer_id', $installer->getTable('poll_answer'), 'answer_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Poll Vote');
$installer->getConnection()->createTable($table);

$installer->endSetup();
