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
 * @package     Magento_Oauth
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Installation of OAuth module tables
 */
/** @var $install \Magento\Core\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();

/** @var $adapter \Magento\DB\Adapter\Pdo\Mysql */
$adapter = $installer->getConnection();

/**
 * Create table 'oauth_consumer'
 */
$table = $adapter->newTable($installer->getTable('oauth_consumer'))
    ->addColumn('entity_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Entity Id')
    ->addColumn('created_at', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default'  => \Magento\DB\Ddl\Table::TIMESTAMP_INIT
        ), 'Created At')
    ->addColumn('updated_at', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            'nullable' => true
        ), 'Updated At')
    ->addColumn('name', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Name of consumer')
    ->addColumn('key', \Magento\DB\Ddl\Table::TYPE_TEXT, \Magento\Oauth\Model\Consumer::KEY_LENGTH, array(
            'nullable' => false
        ), 'Key code')
    ->addColumn('secret', \Magento\DB\Ddl\Table::TYPE_TEXT, \Magento\Oauth\Model\Consumer::SECRET_LENGTH, array(
            'nullable' => false
        ), 'Secret code')
    ->addColumn('callback_url', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(), 'Callback URL')
    ->addColumn('rejected_callback_url', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
            'nullable'  => false
        ), 'Rejected callback URL')
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('oauth_consumer'),
            array('key'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('key'),
        array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('oauth_consumer'),
            array('secret'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('secret'),
        array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('oauth_consumer', array('created_at')), array('created_at'))
    ->addIndex($installer->getIdxName('oauth_consumer', array('updated_at')), array('updated_at'))
    ->setComment('OAuth Consumers');
$adapter->createTable($table);

/**
 * Create table 'oauth_token'
 */
$table = $adapter->newTable($installer->getTable('oauth_token'))
    ->addColumn('entity_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary'  => true,
        ), 'Entity ID')
    ->addColumn('consumer_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false
        ), 'Consumer ID')
    ->addColumn('admin_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true
        ), 'Admin user ID')
    ->addColumn('customer_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true
        ), 'Customer user ID')
    ->addColumn('type', \Magento\DB\Ddl\Table::TYPE_TEXT, 16, array(
            'nullable' => false
        ), 'Token Type')
    ->addColumn('token', \Magento\DB\Ddl\Table::TYPE_TEXT, \Magento\Oauth\Model\Token::LENGTH_TOKEN, array(
            'nullable' => false
        ), 'Token')
    ->addColumn('secret', \Magento\DB\Ddl\Table::TYPE_TEXT, \Magento\Oauth\Model\Token::LENGTH_SECRET, array(
            'nullable' => false
        ), 'Token Secret')
    ->addColumn('verifier', \Magento\DB\Ddl\Table::TYPE_TEXT, \Magento\Oauth\Model\Token::LENGTH_VERIFIER, array(
            'nullable' => true
        ), 'Token Verifier')
    ->addColumn('callback_url', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Token Callback URL')
    ->addColumn('revoked', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default'  => 0,
        ), 'Is Token revoked')
    ->addColumn('authorized', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default'  => 0,
        ), 'Is Token authorized')
    ->addColumn('created_at', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default'  => \Magento\DB\Ddl\Table::TIMESTAMP_INIT
        ), 'Token creation timestamp')
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('oauth_token'),
            array('consumer_id'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
        ),
        array('consumer_id'),
        array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX))
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('oauth_token'),
            array('token'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('token'),
        array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $installer->getFkName('oauth_token', 'admin_id', $installer->getTable('admin_user'), 'user_id'),
        'admin_id',
        $installer->getTable('admin_user'),
        'user_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('oauth_token', 'consumer_id', $installer->getTable('oauth_consumer'), 'entity_id'),
        'consumer_id',
        $installer->getTable('oauth_consumer'),
        'entity_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('oauth_token', 'customer_id', $installer->getTable('customer_entity'), 'entity_id'),
        'customer_id',
        $installer->getTable('customer_entity'),
        'entity_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->setComment('OAuth Tokens');
$adapter->createTable($table);

/**
 * Create table 'oauth_nonce
 */
$table = $adapter->newTable($installer->getTable('oauth_nonce'))
    ->addColumn('nonce', \Magento\DB\Ddl\Table::TYPE_TEXT, 32, array(
        'nullable' => false
        ), 'Nonce String')
    ->addColumn('timestamp', \Magento\DB\Ddl\Table::TYPE_INTEGER, 10, array(
            'unsigned' => true,
            'nullable' => false
        ), 'Nonce Timestamp')
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('oauth_nonce'),
            array('nonce'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('nonce'),
        array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->setOption('type', 'MyISAM');
$adapter->createTable($table);

$installer->endSetup();
