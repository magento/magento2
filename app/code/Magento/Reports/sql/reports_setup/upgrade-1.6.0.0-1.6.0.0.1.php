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

/** @var $installer \Magento\Framework\Module\Setup */
$installer = $this;
/*
 * Prepare database for tables install
 */
$installer->startSetup();

$aggregationTables = array(
    'report_viewed_product_aggregated_daily',
    'report_viewed_product_aggregated_monthly',
    'report_viewed_product_aggregated_yearly'
);
$aggregationTableComments = array(
    'Most Viewed Products Aggregated Daily',
    'Most Viewed Products Aggregated Monthly',
    'Most Viewed Products Aggregated Yearly'
);

for ($i = 0; $i < 3; ++$i) {
    $table = $installer->getConnection()->newTable(
        $installer->getTable($aggregationTables[$i])
    )->addColumn(
        'id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
        'Id'
    )->addColumn(
        'period',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
        null,
        array(),
        'Period'
    )->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array('unsigned' => true),
        'Store Id'
    )->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('unsigned' => true),
        'Product Id'
    )->addColumn(
        'product_name',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        array('nullable' => true),
        'Product Name'
    )->addColumn(
        'product_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        array('nullable' => false, 'default' => '0.0000'),
        'Product Price'
    )->addColumn(
        'views_num',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array('nullable' => false, 'default' => '0'),
        'Number of Views'
    )->addColumn(
        'rating_pos',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array('unsigned' => true, 'nullable' => false, 'default' => '0'),
        'Rating Pos'
    )->addIndex(
        $installer->getIdxName(
            $aggregationTables[$i],
            array('period', 'store_id', 'product_id'),
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'product_id'),
        array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
    )->addIndex(
        $installer->getIdxName($aggregationTables[$i], array('store_id')),
        array('store_id')
    )->addIndex(
        $installer->getIdxName($aggregationTables[$i], array('product_id')),
        array('product_id')
    )->addForeignKey(
        $installer->getFkName($aggregationTables[$i], 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->addForeignKey(
        $installer->getFkName($aggregationTables[$i], 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )->setComment(
        $aggregationTableComments[$i]
    );
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
