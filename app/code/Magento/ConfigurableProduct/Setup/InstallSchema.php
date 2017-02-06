<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'catalog_product_super_attribute'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_super_attribute'))
            ->addColumn(
                'product_super_attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Super Attribute ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Position'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalog_product_super_attribute',
                    ['product_id', 'attribute_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id', 'attribute_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_super_attribute',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Super Attribute Table');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_super_attribute_label'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_super_attribute_label'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'product_super_attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Super Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'use_default',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Use Default Value'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalog_product_super_attribute_label',
                    ['product_super_attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_super_attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_super_attribute_label', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_super_attribute_label',
                    'product_super_attribute_id',
                    'catalog_product_super_attribute',
                    'product_super_attribute_id'
                ),
                'product_super_attribute_id',
                $installer->getTable('catalog_product_super_attribute'),
                'product_super_attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('catalog_product_super_attribute_label', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Super Attribute Label Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_super_link'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_super_link'))
            ->addColumn(
                'link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Link ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Parent ID'
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_super_link', ['parent_id']),
                ['parent_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalog_product_super_link',
                    ['product_id', 'parent_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id', 'parent_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_super_link',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('catalog_product_super_link', 'parent_id', 'catalog_product_entity', 'entity_id'),
                'parent_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Super Link Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
