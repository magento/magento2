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

/**
 * Create table 'email_template'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('email_template')
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
    array('nullable' => false),
    'Template Name'
)->addColumn(
    'template_text',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => false),
    'Template Content'
)->addColumn(
    'template_styles',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Templste Styles'
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
    array('nullable' => false),
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
    'added_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Date of Template Creation'
)->addColumn(
    'modified_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Date of Template Modification'
)->addColumn(
    'orig_template_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    array(),
    'Original Template Code'
)->addColumn(
    'orig_template_variables',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Original Template Variables'
)->addIndex(
    $installer->getIdxName(
        'email_template',
        array('template_code'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('template_code'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('email_template', array('added_at')),
    array('added_at')
)->addIndex(
    $installer->getIdxName('email_template', array('modified_at')),
    array('modified_at')
)->setComment(
    'Email Templates'
);

$installer->getConnection()->createTable($table);
