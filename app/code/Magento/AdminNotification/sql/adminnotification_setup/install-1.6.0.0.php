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

/**
 * AdminNotification install
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
$installer = $this;
/* @var $installer \Magento\Framework\Module\Setup */

$installer->startSetup();
/**
 * Create table 'adminnotification_inbox'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('adminnotification_inbox')
)->addColumn(
    'notification_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Notification id'
)->addColumn(
    'severity',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Problem type'
)->addColumn(
    'date_added',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Create date'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Title'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Description'
)->addColumn(
    'url',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Url'
)->addColumn(
    'is_read',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Flag if notification read'
)->addColumn(
    'is_remove',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Flag if notification might be removed'
)->addIndex(
    $installer->getIdxName('adminnotification_inbox', array('severity')),
    array('severity')
)->addIndex(
    $installer->getIdxName('adminnotification_inbox', array('is_read')),
    array('is_read')
)->addIndex(
    $installer->getIdxName('adminnotification_inbox', array('is_remove')),
    array('is_remove')
)->setComment(
    'Adminnotification Inbox'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
