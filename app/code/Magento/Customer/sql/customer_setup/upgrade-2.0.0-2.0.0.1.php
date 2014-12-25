<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableNames = [
    'customer_address_entity_varchar', 'customer_address_entity_datetime',
    'customer_address_entity_decimal', 'customer_address_entity_int', 'customer_address_entity_text',
    'customer_entity_varchar', 'customer_entity_datetime',
    'customer_entity_decimal', 'customer_entity_int', 'customer_entity_text'
];

foreach ($tableNames as $table) {
    $connection->dropForeignKey(
        $installer->getTable($table),
        $installer->getFkName($table, 'entity_type_id', 'eav_entity_type', 'entity_type_id')
    );
    $connection->dropIndex(
        $installer->getTable($table),
        $installer->getIdxName(
            $installer->getTable($table),
            ['entity_type_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        )
    );
    $connection->dropColumn($installer->getTable($table), 'entity_type_id');
}

$connection->dropColumn($installer->getTable('customer_address_entity'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity'), 'attribute_set_id');

$connection->dropIndex(
    $installer->getTable('customer_entity'),
    $installer->getIdxName('customer_entity', ['entity_type_id'])
);
$connection->dropColumn($installer->getTable('customer_entity'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_entity'), 'attribute_set_id');
$installer->endSetup();
