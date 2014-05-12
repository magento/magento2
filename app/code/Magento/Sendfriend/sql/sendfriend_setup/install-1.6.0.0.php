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

$installer = $this;
/* @var $installer \Magento\Framework\Module\Setup */

$installer->startSetup();

$table = $installer->getConnection()->newTable(
    $installer->getTable('sendfriend_log')
)->addColumn(
    'log_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Log ID'
)->addColumn(
    'ip',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    '20',
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer IP address'
)->addColumn(
    'time',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Log time'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Website ID'
)->addIndex(
    $installer->getIdxName('sendfriend_log', 'ip'),
    'ip'
)->addIndex(
    $installer->getIdxName('sendfriend_log', 'time'),
    'time'
)->setComment(
    'Send to friend function log storage table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
