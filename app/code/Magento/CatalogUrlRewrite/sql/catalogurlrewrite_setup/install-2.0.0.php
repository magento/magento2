<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Framework\Module\Setup */
$installer = $this;

$installer->startSetup();

$tableName = \Magento\CatalogUrlRewrite\Model\Resource\Category\Product::TABLE_NAME;
$table = $installer->getConnection()
    ->newTable($installer->getTable($tableName))
    ->addColumn(
        'url_rewrite_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false],
        'url_rewrite_id'
    )
    ->addColumn(
        'category_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false],
        'category_id'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false],
        'product_id'
    )
    ->addIndex(
        $installer->getIdxName($tableName, ['category_id', 'product_id']),
        ['category_id', 'product_id']
    )
    ->addForeignKey(
        $installer->getFkName($tableName, 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName($tableName, 'category_id', 'catalog_category_entity', 'entity_id'),
        'category_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName($tableName, 'url_rewrite_id', 'url_rewrite', 'url_rewrite_id'),
        'url_rewrite_id',
        $installer->getTable('url_rewrite'),
        'url_rewrite_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('url_rewrite_relation');
$installer->getConnection()->createTable($table);

$installer->endSetup();
