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

/**
 * Create table 'cron_schedule'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('cron_schedule')
)->addColumn(
    'schedule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Schedule Id'
)->addColumn(
    'job_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false, 'default' => '0'),
    'Job Code'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    7,
    array('nullable' => false, 'default' => 'pending'),
    'Status'
)->addColumn(
    'messages',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Messages'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Created At'
)->addColumn(
    'scheduled_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => true),
    'Scheduled At'
)->addColumn(
    'executed_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => true),
    'Executed At'
)->addColumn(
    'finished_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => true),
    'Finished At'
)->addIndex(
    $installer->getIdxName('cron_schedule', array('job_code')),
    array('job_code')
)->addIndex(
    $installer->getIdxName('cron_schedule', array('scheduled_at', 'status')),
    array('scheduled_at', 'status')
)->setComment(
    'Cron Schedule'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
