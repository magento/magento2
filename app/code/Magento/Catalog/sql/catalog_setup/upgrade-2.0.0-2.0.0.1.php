<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

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
