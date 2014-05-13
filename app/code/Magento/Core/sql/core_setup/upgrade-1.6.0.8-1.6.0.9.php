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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer \Magento\Framework\Module\Setup */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Create table 'core_theme_files'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_theme_files')
)->addColumn(
    'theme_files_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Theme files identifier'
)->addColumn(
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Theme Id'
)->addColumn(
    'file_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'File Name'
)->addColumn(
    'file_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => false),
    'File Type'
)->addColumn(
    'content',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
    array('nullable' => false),
    'File Content'
)->addColumn(
    'order',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => 0),
    'Order'
)->addForeignKey(
    $installer->getFkName('core_theme_files', 'theme_id', 'core_theme', 'theme_id'),
    'theme_id',
    $installer->getTable('core_theme'),
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Core theme files'
);

$installer->getConnection()->createTable($table);

$installer->endSetup();
