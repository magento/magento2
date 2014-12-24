<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_varchar'),
    $installer->getFkName('customer_address_entity_varchar', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_varchar',
    $installer->getIdxName('customer_address_entity_varchar', 'entity_type_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_datetime'),
    $installer->getFkName('customer_address_entity_datetime', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_datetime',
    $installer->getIdxName('customer_address_entity_datetime', 'entity_type_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_decimal'),
    $installer->getFkName('customer_address_entity_decimal', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_decimal',
    $installer->getIdxName('customer_address_entity_decimal', 'entity_type_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_int'),
    $installer->getFkName('customer_address_entity_int', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_int',
    $installer->getIdxName('customer_address_entity_int', 'entity_type_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_text'),
    $installer->getFkName('customer_address_entity_text', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_text',
    $installer->getIdxName('customer_address_entity_text', 'entity_type_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
);
$connection->dropColumn($installer->getTable('customer_address_entity'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity'), 'attribute_set_id');
$connection->dropColumn($installer->getTable('customer_address_entity_datetime'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_decimal'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_int'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_text'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_varchar'), 'entity_type_id');
$installer->endSetup();