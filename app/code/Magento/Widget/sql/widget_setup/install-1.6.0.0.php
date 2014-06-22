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
 * Create table 'widget'
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('widget'))) {
    $table = $installer->getConnection()->newTable(
        $installer->getTable('widget')
    )->addColumn(
        'widget_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
        'Widget Id'
    )->addColumn(
        'widget_code',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        array(),
        'Widget code for template directive'
    )->addColumn(
        'widget_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        array(),
        'Widget Type'
    )->addColumn(
        'parameters',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        '64k',
        array('nullable' => true),
        'Parameters'
    )->addIndex(
        $installer->getIdxName('widget', 'widget_code'),
        'widget_code'
    )->setComment(
        'Preconfigured Widgets'
    );
    $installer->getConnection()->createTable($table);
} else {

    $installer->getConnection()->dropIndex($installer->getTable('widget'), 'IDX_CODE');

    $tables = array(
        $installer->getTable(
            'widget'
        ) => array(
            'columns' => array(
                'widget_id' => array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment' => 'Widget Id'
                ),
                'parameters' => array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64K',
                    'comment' => 'Parameters'
                )
            ),
            'comment' => 'Preconfigured Widgets'
        )
    );

    $installer->getConnection()->modifyTables($tables);

    $installer->getConnection()->changeColumn(
        $installer->getTable('widget'),
        'code',
        'widget_code',
        array(
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Widget code for template directive'
        )
    );

    $installer->getConnection()->changeColumn(
        $installer->getTable('widget'),
        'type',
        'widget_type',
        array('type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'comment' => 'Widget Type')
    );

    $installer->getConnection()->addIndex(
        $installer->getTable('widget'),
        $installer->getIdxName('widget', array('widget_code')),
        array('widget_code')
    );
}

/**
 * Create table 'widget_instance'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('widget_instance')
)->addColumn(
    'instance_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Instance Id'
)->addColumn(
    'instance_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Instance Type'
)->addColumn(
    'package_theme',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Package Theme'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Widget Title'
)->addColumn(
    'store_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false, 'default' => '0'),
    'Store ids'
)->addColumn(
    'widget_parameters',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Widget parameters'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Sort order'
)->setComment(
    'Instances of Widget for Package Theme'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'widget_instance_page'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('widget_instance_page')
)->addColumn(
    'page_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Page Id'
)->addColumn(
    'instance_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Instance Id'
)->addColumn(
    'page_group',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    25,
    array(),
    'Block Group Type'
)->addColumn(
    'layout_handle',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Layout Handle'
)->addColumn(
    'block_reference',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Container'
)->addColumn(
    'page_for',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    25,
    array(),
    'For instance entities'
)->addColumn(
    'entities',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Catalog entities (comma separated)'
)->addColumn(
    'page_template',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Path to widget template'
)->addIndex(
    $installer->getIdxName('widget_instance_page', 'instance_id'),
    'instance_id'
)->addForeignKey(
    $installer->getFkName('widget_instance_page', 'instance_id', 'widget_instance', 'instance_id'),
    'instance_id',
    $installer->getTable('widget_instance'),
    'instance_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Instance of Widget on Page'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'widget_instance_page_layout'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('widget_instance_page_layout')
)->addColumn(
    'page_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Page Id'
)->addColumn(
    'layout_update_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Layout Update Id'
)->addIndex(
    $installer->getIdxName('widget_instance_page_layout', 'page_id'),
    'page_id'
)->addIndex(
    $installer->getIdxName(
        'widget_instance_page_layout',
        array('layout_update_id', 'page_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('layout_update_id', 'page_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addForeignKey(
    $installer->getFkName('widget_instance_page_layout', 'page_id', 'widget_instance_page', 'page_id'),
    'page_id',
    $installer->getTable('widget_instance_page'),
    'page_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('widget_instance_page_layout', 'layout_update_id', 'core_layout_update', 'layout_update_id'),
    'layout_update_id',
    $installer->getTable('core_layout_update'),
    'layout_update_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Layout updates'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
