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

            //Drop entity_type_id column for catalog product entities
            $connection->dropColumn($installer->getTable('catalog_product_entity'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_product_entity_datetime'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_product_entity_decimal'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_product_entity_gallery'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_product_entity_int'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_product_entity_text'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_product_entity_varchar'), 'entity_type_id');

            //Drop entity_type_id column for catalog category entities
            $connection->dropColumn($installer->getTable('catalog_category_entity'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_category_entity_datetime'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_category_entity_decimal'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_category_entity_int'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_category_entity_text'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('catalog_category_entity_varchar'), 'entity_type_id');

            $installer->endSetup();
        }
    }
}
