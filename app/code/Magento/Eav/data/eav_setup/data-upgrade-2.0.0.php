<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Eav\Model\Entity\Setup  */
$installer = $this;
$installer->startSetup();
/** @var \Magento\Framework\Module\Setup\Migration $migrationSetup */
$migrationSetup = $installer->createMigrationSetup();

$migrationSetup->appendClassAliasReplace(
    'eav_attribute',
    'attribute_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['attribute_id']
);
$migrationSetup->appendClassAliasReplace(
    'eav_attribute',
    'backend_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['attribute_id']
);
$migrationSetup->appendClassAliasReplace(
    'eav_attribute',
    'frontend_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['attribute_id']
);
$migrationSetup->appendClassAliasReplace(
    'eav_attribute',
    'source_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['attribute_id']
);

$migrationSetup->appendClassAliasReplace(
    'eav_entity_type',
    'entity_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['entity_type_id']
);
$migrationSetup->appendClassAliasReplace(
    'eav_entity_type',
    'attribute_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['entity_type_id']
);
$migrationSetup->appendClassAliasReplace(
    'eav_entity_type',
    'increment_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['entity_type_id']
);
$migrationSetup->appendClassAliasReplace(
    'eav_entity_type',
    'entity_attribute_collection',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['entity_type_id']
);

$migrationSetup->doUpdateClassAliases();

/** @var $groups \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection*/
$groups = $installer->getAttributeGroupCollectionFactory();
foreach ($groups as $group) {
    /** @var $group \Magento\Eav\Model\Entity\Attribute\Group*/
    $group->save();
}

$installer->endSetup();
