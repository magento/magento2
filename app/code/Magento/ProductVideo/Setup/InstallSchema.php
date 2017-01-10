<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

/**
 * Class InstallSchema adds new table `eav_attribute_option_swatch`
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Video Data Table name
     */
    const GALLERY_VALUE_VIDEO_TABLE = 'catalog_product_entity_media_gallery_value_video';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $contextInterface)
    {
        $setup->startSetup();

        /**
         * Create table 'catalog_product_entity_media_gallery_value_video'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::GALLERY_VALUE_VIDEO_TABLE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Media Entity ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'provider',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => true, 'default' => null],
                'Video provider ID'
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Video URL'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Title'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Page Meta Description'
            )
            ->addColumn(
                'metadata',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Video meta data'
            )
            ->addIndex(
                $setup->getIdxName(
                    self::GALLERY_VALUE_VIDEO_TABLE,
                    ['value_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['value_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    self::GALLERY_VALUE_VIDEO_TABLE,
                    'value_id',
                    Gallery::GALLERY_TABLE,
                    'value_id'
                ),
                'value_id',
                $setup->getTable(Gallery::GALLERY_TABLE),
                'value_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    self::GALLERY_VALUE_VIDEO_TABLE,
                    'store_id',
                    $setup->getTable('store'),
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Video Table');

        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
