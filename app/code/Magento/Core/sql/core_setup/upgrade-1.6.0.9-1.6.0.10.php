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

$connection->addColumn(
    $installer->getTable('core_theme_files'),
    'is_temporary',
    array(
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Is Temporary File'
    )
);

$connection->changeColumn(
    $installer->getTable('core_theme_files'),
    'file_name',
    'file_path',
    array(
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => true,
        'comment' => 'Relative path to file'
    )
);

$connection->changeColumn(
    $installer->getTable('core_theme_files'),
    'order',
    'sort_order',
    array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT)
);

/**
 * Create table 'core_theme_files_link'
 */
$table = $connection->newTable(
    $installer->getTable('core_theme_files_link')
)->addColumn(
    'files_link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true),
    'Customization link id'
)->addColumn(
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Theme Id'
)->addColumn(
    'layout_link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Theme layout link id'
)->addForeignKey(
    $installer->getFkName('core_theme_files_link', 'theme_id', 'core_theme', 'theme_id'),
    'theme_id',
    $installer->getTable('core_theme'),
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Core theme link on layout update'
);

$installer->getConnection()->createTable($table);

$installer->endSetup();
