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
 * Newsletter install
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
$installer = $this;
/* @var $installer \Magento\Framework\Module\Setup */

$installer->startSetup();

/**
 * Create table 'newsletter_subscriber'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('newsletter_subscriber')
)->addColumn(
    'subscriber_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Subscriber Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Store Id'
)->addColumn(
    'change_status_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Change Status At'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer Id'
)->addColumn(
    'subscriber_email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    150,
    array('nullable' => true, 'default' => null),
    'Subscriber Email'
)->addColumn(
    'subscriber_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Subscriber Status'
)->addColumn(
    'subscriber_confirm_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('default' => 'NULL'),
    'Subscriber Confirm Code'
)->addIndex(
    $installer->getIdxName('newsletter_subscriber', array('customer_id')),
    array('customer_id')
)->addIndex(
    $installer->getIdxName('newsletter_subscriber', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('newsletter_subscriber', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Newsletter Subscriber'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'newsletter_template'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('newsletter_template')
)->addColumn(
    'template_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Template Id'
)->addColumn(
    'template_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    150,
    array(),
    'Template Code'
)->addColumn(
    'template_text',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Template Text'
)->addColumn(
    'template_text_preprocessed',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Template Text Preprocessed'
)->addColumn(
    'template_styles',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Template Styles'
)->addColumn(
    'template_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Template Type'
)->addColumn(
    'template_subject',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Template Subject'
)->addColumn(
    'template_sender_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Template Sender Name'
)->addColumn(
    'template_sender_email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Template Sender Email'
)->addColumn(
    'template_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '1'),
    'Template Actual'
)->addColumn(
    'added_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Added At'
)->addColumn(
    'modified_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Modified At'
)->addIndex(
    $installer->getIdxName('newsletter_template', array('template_actual')),
    array('template_actual')
)->addIndex(
    $installer->getIdxName('newsletter_template', array('added_at')),
    array('added_at')
)->addIndex(
    $installer->getIdxName('newsletter_template', array('modified_at')),
    array('modified_at')
)->setComment(
    'Newsletter Template'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'newsletter_queue'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('newsletter_queue')
)->addColumn(
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Queue Id'
)->addColumn(
    'template_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Template Id'
)->addColumn(
    'newsletter_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array(),
    'Newsletter Type'
)->addColumn(
    'newsletter_text',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Newsletter Text'
)->addColumn(
    'newsletter_styles',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Newsletter Styles'
)->addColumn(
    'newsletter_subject',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Newsletter Subject'
)->addColumn(
    'newsletter_sender_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Newsletter Sender Name'
)->addColumn(
    'newsletter_sender_email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Newsletter Sender Email'
)->addColumn(
    'queue_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Queue Status'
)->addColumn(
    'queue_start_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Queue Start At'
)->addColumn(
    'queue_finish_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Queue Finish At'
)->addIndex(
    $installer->getIdxName('newsletter_queue', array('template_id')),
    array('template_id')
)->addForeignKey(
    $installer->getFkName('newsletter_queue', 'template_id', 'newsletter_template', 'template_id'),
    'template_id',
    $installer->getTable('newsletter_template'),
    'template_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Newsletter Queue'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'newsletter_queue_link'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('newsletter_queue_link')
)->addColumn(
    'queue_link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Queue Link Id'
)->addColumn(
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Queue Id'
)->addColumn(
    'subscriber_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Subscriber Id'
)->addColumn(
    'letter_sent_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Letter Sent At'
)->addIndex(
    $installer->getIdxName('newsletter_queue_link', array('subscriber_id')),
    array('subscriber_id')
)->addIndex(
    $installer->getIdxName('newsletter_queue_link', array('queue_id', 'letter_sent_at')),
    array('queue_id', 'letter_sent_at')
)->addForeignKey(
    $installer->getFkName('newsletter_queue_link', 'queue_id', 'newsletter_queue', 'queue_id'),
    'queue_id',
    $installer->getTable('newsletter_queue'),
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('newsletter_queue_link', 'subscriber_id', 'newsletter_subscriber', 'subscriber_id'),
    'subscriber_id',
    $installer->getTable('newsletter_subscriber'),
    'subscriber_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Newsletter Queue Link'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'newsletter_queue_store_link'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('newsletter_queue_store_link')
)->addColumn(
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Queue Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Store Id'
)->addIndex(
    $installer->getIdxName('newsletter_queue_store_link', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('newsletter_queue_store_link', 'queue_id', 'newsletter_queue', 'queue_id'),
    'queue_id',
    $installer->getTable('newsletter_queue'),
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('newsletter_queue_store_link', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Newsletter Queue Store Link'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'newsletter_problem'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('newsletter_problem')
)->addColumn(
    'problem_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Problem Id'
)->addColumn(
    'subscriber_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Subscriber Id'
)->addColumn(
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Queue Id'
)->addColumn(
    'problem_error_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Problem Error Code'
)->addColumn(
    'problem_error_text',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Problem Error Text'
)->addIndex(
    $installer->getIdxName('newsletter_problem', array('subscriber_id')),
    array('subscriber_id')
)->addIndex(
    $installer->getIdxName('newsletter_problem', array('queue_id')),
    array('queue_id')
)->addForeignKey(
    $installer->getFkName('newsletter_problem', 'queue_id', 'newsletter_queue', 'queue_id'),
    'queue_id',
    $installer->getTable('newsletter_queue'),
    'queue_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('newsletter_problem', 'subscriber_id', 'newsletter_subscriber', 'subscriber_id'),
    'subscriber_id',
    $installer->getTable('newsletter_subscriber'),
    'subscriber_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Newsletter Problems'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
