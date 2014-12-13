<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
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
        ['store_id', 'package', 'theme', 'layout_update_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropColumn($tableCoreLayoutLink, 'area');

$connection->dropColumn($tableCoreLayoutLink, 'package');

$connection->changeColumn(
    $tableCoreLayoutLink,
    'theme',
    'theme_id',
    [
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'comment' => 'Theme id'
    ]
);

$connection->addIndex(
    $tableCoreLayoutLink,
    $installer->getIdxName(
        'core_layout_link',
        ['store_id', 'theme_id', 'layout_update_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['store_id', 'theme_id', 'layout_update_id'],
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
    [
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        'length' => '255',
        'nullable' => false,
        'comment' => 'Theme Area'
    ]
);

$installer->endSetup();
