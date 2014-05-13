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
 * Modifying 'core_layout_link' table. Replace columns area, package, theme to theme_id
 */
$tableCoreLayoutLink = $installer->getTable('core_layout_link');

$connection->dropForeignKey(
    $tableCoreLayoutLink,
    $installer->getFkName('core_layout_link', 'store_id', 'store', 'store_id')
);

$connection->dropIndex(
    $tableCoreLayoutLink,
    $installer->getIdxName(
        'core_layout_link',
        array('store_id', 'package', 'theme', 'layout_update_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropColumn($tableCoreLayoutLink, 'area');

$connection->dropColumn($tableCoreLayoutLink, 'package');

$connection->changeColumn(
    $tableCoreLayoutLink,
    'theme',
    'theme_id',
    array(
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'comment' => 'Theme id'
    )
);

$connection->addIndex(
    $tableCoreLayoutLink,
    $installer->getIdxName(
        'core_layout_link',
        array('store_id', 'theme_id', 'layout_update_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('store_id', 'theme_id', 'layout_update_id'),
    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
);

$connection->addForeignKey(
    $installer->getFkName('core_layout_link', 'store_id', 'store', 'store_id'),
    $tableCoreLayoutLink,
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
);

$connection->addForeignKey(
    $installer->getFkName('core_layout_link', 'theme_id', 'core_theme', 'theme_id'),
    $tableCoreLayoutLink,
    'theme_id',
    $installer->getTable('core_theme'),
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
);

/**
 * Add column 'area' to 'core_theme'
 */
$connection->addColumn(
    $installer->getTable('core_theme'),
    'area',
    array(
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        'length' => '255',
        'nullable' => false,
        'comment' => 'Theme Area'
    )
);

$installer->endSetup();
