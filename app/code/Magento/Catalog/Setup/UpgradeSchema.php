<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.0.1') < 0) {
            $installer = $setup;

            $installer->startSetup();

            $connection = $installer->getConnection();
            $connection->dropForeignKey(
                $installer->getTable('catalog_product_entity'),
                'FK_CAT_PRD_ENTT_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID'
            );

            $dropTablesColumn = [
                'catalog_product_entity',
                'catalog_product_entity_datetime',
                'catalog_product_entity_decimal',
                'catalog_product_entity_gallery',
                'catalog_product_entity_int',
                'catalog_product_entity_text',
                'catalog_product_entity_varchar',
                'catalog_category_entity',
                'catalog_category_entity_datetime',
                'catalog_category_entity_decimal',
                'catalog_category_entity_int',
                'catalog_category_entity_text',
                'catalog_category_entity_varchar'
            ];
            foreach ($dropTablesColumn as $table) {
                $connection->dropIndex(
                    $installer->getTable($table),
                    $installer->getIdxName(
                        $table,
                        'entity_type_id',
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    )
                );
                $connection->dropColumn($installer->getTable($table), 'entity_type_id');
            }

            $installer->endSetup();
        }
    }
}
