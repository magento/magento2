<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

//Drop entity_type_id and attribute_set_id from column for customer_address_entity
$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_varchar'),
    $installer->getFkName('customer_address_entity_varchar', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_varchar',
    $installer->getIdxName(
        'customer_address_entity_varchar',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_datetime'),
    $installer->getFkName('customer_address_entity_datetime', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_datetime',
    $installer->getIdxName(
        'customer_address_entity_datetime',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_decimal'),
    $installer->getFkName('customer_address_entity_decimal', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_decimal',
    $installer->getIdxName(
        'customer_address_entity_decimal',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_int'),
    $installer->getFkName('customer_address_entity_int', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_int',
    $installer->getIdxName(
        'customer_address_entity_int',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_address_entity_text'),
    $installer->getFkName('customer_address_entity_text', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_address_entity_text',
    $installer->getIdxName(
        'customer_address_entity_text',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);
$connection->dropColumn($installer->getTable('customer_address_entity'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity'), 'attribute_set_id');
$connection->dropColumn($installer->getTable('customer_address_entity_datetime'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_decimal'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_int'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_text'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_address_entity_varchar'), 'entity_type_id');

//Drop entity_type_id and attribute_set_id from column for customer_entity
$connection->dropIndex(
    'customer_entity',
    $installer->getIdxName('customer_entity', ['entity_type_id'])
);

$connection->dropForeignKey(
    $installer->getTable('customer_entity_varchar'),
    $installer->getFkName('customer_entity_varchar', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_entity_varchar',
    $installer->getIdxName(
        'customer_entity_varchar',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_entity_datetime'),
    $installer->getFkName('customer_entity_datetime', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_entity_datetime',
    $installer->getIdxName(
        'customer_entity_datetime',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_entity_decimal'),
    $installer->getFkName('customer_entity_decimal', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_entity_decimal',
    $installer->getIdxName(
        'customer_entity_decimal',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);

$connection->dropForeignKey(
    $installer->getTable('customer_entity_int'),
    $installer->getFkName('customer_entity_int', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_entity_int',
    $installer->getIdxName('customer_entity_int', ['entity_type_id'])
);

$connection->dropForeignKey(
    $installer->getTable('customer_entity_text'),
    $installer->getFkName('customer_entity_text', 'entity_type_id', 'eav_entity_type', 'entity_type_id')
);
$connection->dropIndex(
    'customer_entity_text',
    $installer->getIdxName(
        'customer_entity_text',
        ['entity_type_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    )
);
$connection->dropColumn($installer->getTable('customer_entity'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_entity'), 'attribute_set_id');
$connection->dropColumn($installer->getTable('customer_entity_datetime'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_entity_decimal'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_entity_int'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_entity_text'), 'entity_type_id');
$connection->dropColumn($installer->getTable('customer_entity_varchar'), 'entity_type_id');
$installer->endSetup();
