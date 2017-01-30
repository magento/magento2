<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;

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
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addSupportVideoMediaAttributes($setup);
            $this->removeGroupPrice($setup);
        }

        $setup->endSetup();
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createValueToEntityTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'catalog_product_entity_media_gallery_value_to_entity'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(Media::GALLERY_VALUE_TO_ENTITY_TABLE))
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
                $setup->getIdxName(
                    Media::GALLERY_VALUE_TO_ENTITY_TABLE,
                    ['value_id', 'entity_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['value_id', 'entity_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    Media::GALLERY_VALUE_TO_ENTITY_TABLE,
                    'value_id',
                    Media::GALLERY_TABLE,
                    'value_id'
                ),
                'value_id',
                $setup->getTable(Media::GALLERY_TABLE),
                'value_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    Media::GALLERY_VALUE_TO_ENTITY_TABLE,
                    'entity_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'entity_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Link Media value to Product entity table');
        $setup->getConnection()->createTable($table);
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addForeignKeys(SchemaSetupInterface $setup)
    {
        /**
         * Add foreign keys again
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                Media::GALLERY_VALUE_TABLE,
                'value_id',
                Media::GALLERY_TABLE,
                'value_id'
            ),
            $setup->getTable(Media::GALLERY_VALUE_TABLE),
            'value_id',
            $setup->getTable(Media::GALLERY_TABLE),
            'value_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                Media::GALLERY_VALUE_TABLE,
                'store_id',
                $setup->getTable('store'),
                'store_id'
            ),
            $setup->getTable(Media::GALLERY_VALUE_TABLE),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addSupportVideoMediaAttributes(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists(Media::GALLERY_VALUE_TO_ENTITY_TABLE)) {
            return;
        };

        /** Add support video media attribute */
        $this->createValueToEntityTable($setup);
        /**
         * Add media type property to the Gallery entry table
         */
        $setup->getConnection()->addColumn(
            $setup->getTable(Media::GALLERY_TABLE),
            'media_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'default' => ImageEntryConverter::MEDIA_TYPE_CODE,
                'comment' => 'Media entry type'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable(Media::GALLERY_TABLE),
            'disabled',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Visibility status'
            ]
        );

        /**
         * Drop entity Id columns
         */
        $setup->getConnection()->dropColumn($setup->getTable(Media::GALLERY_TABLE), 'entity_id');

        /**
         * Drop primary index
         */
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Media::GALLERY_VALUE_TABLE),
            $setup->getFkName(
                Media::GALLERY_VALUE_TABLE,
                'value_id',
                Media::GALLERY_TABLE,
                'value_id'
            )
        );
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Media::GALLERY_VALUE_TABLE),
            $setup->getFkName(
                Media::GALLERY_VALUE_TABLE,
                'store_id',
                'store',
                'store_id'
            )
        );
        $setup->getConnection()->dropIndex($setup->getTable(Media::GALLERY_VALUE_TABLE), 'primary');
        $setup->getConnection()->addColumn(
            $setup->getTable(Media::GALLERY_VALUE_TABLE),
            'record_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'primary' => true,
                'auto_increment' => true,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Record Id'
            ]
        );

        /**
         * Add index 'value_id'
         */
        $setup->getConnection()->addIndex(
            $setup->getTable(Media::GALLERY_VALUE_TABLE),
            $setup->getConnection()->getIndexName(
                $setup->getTable(Media::GALLERY_VALUE_TABLE),
                'value_id',
                'index'
            ),
            'value_id'
        );
        $this->addForeignKeys($setup);
    }

    /**
     * Remove Group Price
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeGroupPrice(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $fields = [
            ['table' => 'catalog_product_index_price_final_idx', 'column' => 'base_group_price'],
            ['table' => 'catalog_product_index_price_final_tmp', 'column' => 'base_group_price'],
            ['table' => 'catalog_product_index_price', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_agr_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_agr_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_final_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_final_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_agr_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_agr_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_tmp', 'column' => 'group_price'],
        ];

        foreach ($fields as $filedInfo) {
            $connection->dropColumn($setup->getTable($filedInfo['table']), $filedInfo['column']);
        }
    }
}
