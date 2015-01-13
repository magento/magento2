<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'gift_message'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('gift_message')
)->addColumn(
    'gift_message_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'GiftMessage Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Customer id'
)->addColumn(
    'sender',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sender'
)->addColumn(
    'recipient',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Recipient'
)->addColumn(
    'message',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    null,
    [],
    'Message'
)->setComment(
    'Gift Message'
);

$installer->getConnection()->createTable($table);

$installer->endSetup();
