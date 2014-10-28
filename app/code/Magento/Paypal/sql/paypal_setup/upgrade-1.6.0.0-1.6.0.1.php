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

/** @var $installer \Magento\Sales\Model\Resource\Setup */
$installer = $this;

/**
 * Create table 'paypal_payment_transaction'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('paypal_payment_transaction')
)->addColumn(
    'transaction_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity Id'
)->addColumn(
    'txn_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    array(),
    'Txn Id'
)->addColumn(
    'additional_information',
    \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
    '64K',
    array(),
    'Additional Information'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Created At'
)->addIndex(
    $installer->getIdxName(
        'paypal_payment_transaction',
        array('txn_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('txn_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->setComment(
    'PayPal Payflow Link Payment Transaction'
);
$installer->getConnection()->createTable($table);
