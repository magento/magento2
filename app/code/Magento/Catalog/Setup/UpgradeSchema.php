<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageMediaEntryConverter;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** Add support video media attribute */
        if (version_compare($context->getVersion(), '2.0.0', '<=')) {
            $installer = $setup;
            /**
             * Create table 'catalog_product_entity_media_gallery_value_to_entity'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable(Media::GALLERY_VALUE_TO_ENTITY_TABLE))
                ->addColumn(
                    'value_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Value media Entry ID'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product entity ID'
                )
                ->addIndex(
                    $installer->getIdxName(Media::GALLERY_VALUE_TO_ENTITY_TABLE, ['entity_id']),
                    ['entity_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        Media::GALLERY_VALUE_TO_ENTITY_TABLE,
                        'value_id',
                        Media::GALLERY_TABLE,
                        'value_id'
                    ),
                    'value_id',
                    $installer->getTable(Media::GALLERY_TABLE),
                    'value_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        Media::GALLERY_VALUE_TO_ENTITY_TABLE,
                        'entity_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'entity_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Link Media value to Product entity table');
            $installer->getConnection()->createTable($table);

            /**
             * Add media type property to the Gallery entry table
             */
            $installer->getConnection()->addColumn(
                $installer->getTable(Media::GALLERY_TABLE),
                'media_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => false,
                    'default' => ImageMediaEntryConverter::MEDIA_TYPE_CODE,
                    'comment' => 'Media entry type'
                ]
            );

            /**
             * Drop entity Id columns
             */
            $installer->getConnection()->dropColumn($installer->getTable(Media::GALLERY_TABLE), 'entity_id');
            $installer->getConnection()->dropColumn($installer->getTable(Media::GALLERY_VALUE_TABLE), 'entity_id');

            $installer->endSetup();
        }
    }
}
