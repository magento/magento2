<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'cron_schedule'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cron_schedule')
)->addColumn(
    'schedule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Schedule Id'
)->addColumn(
    'job_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false, 'default' => '0'],
    'Job Code'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    7,
    ['nullable' => false, 'default' => 'pending'],
    'Status'
)->addColumn(
    'messages',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Messages'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Created At'
)->addColumn(
    'scheduled_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Scheduled At'
)->addColumn(
    'executed_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Executed At'
)->addColumn(
    'finished_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Finished At'
)->addIndex(
    $installer->getIdxName('cron_schedule', ['job_code']),
    ['job_code']
)->addIndex(
    $installer->getIdxName('cron_schedule', ['scheduled_at', 'status']),
    ['scheduled_at', 'status']
)->setComment(
    'Cron Schedule'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
