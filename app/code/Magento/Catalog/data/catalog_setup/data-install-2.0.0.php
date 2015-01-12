<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
$installer->installEntities();
// Create Root Catalog Node
$installer->createCategory()
    ->load(1)
    ->setId(1)
    ->setStoreId(0)
    ->setPath(1)
    ->setLevel(0)
    ->setPosition(0)
    ->setChildrenCount(0)
    ->setName('Root Catalog')
    ->setInitialSetupFlag(true)
    ->save();

$category = $installer->createCategory();

$installer->createCategory()
    ->setStoreId(0)
    ->setName('Default Category')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($category->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath('1')
    ->setInitialSetupFlag(true)
    ->save();

$data = [
    'scope' => 'default',
    'scope_id' => 0,
    'path' => \Magento\Catalog\Helper\Category::XML_PATH_CATEGORY_ROOT_ID,
    'value' => $category->getId(),
];
$installer->getConnection()
    ->insertOnDuplicate($installer->getTable('core_config_data'), $data, ['value']);

$installer->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', 'Design', 6);

$entityTypeId = $installer->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// update General Group
//$installer->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'attribute_group_name', 'General Information');
$installer->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'sort_order', '10');

$groups = [
    'display' => ['name' => 'Display Settings', 'sort' => 20, 'id' => null],
    'design' => ['name' => 'Custom Design', 'sort' => 30, 'id' => null],
];

foreach ($groups as $k => $groupProp) {
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupProp['name'], $groupProp['sort']);
    $groups[$k]['id'] = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupProp['name']);
}

// update attributes group and sort
$attributes = [
    'custom_design' => ['group' => 'design', 'sort' => 10],
    // 'custom_design_apply' => array('group' => 'design', 'sort' => 20),
    'custom_design_from' => ['group' => 'design', 'sort' => 30],
    'custom_design_to' => ['group' => 'design', 'sort' => 40],
    'page_layout' => ['group' => 'design', 'sort' => 50],
    'custom_layout_update' => ['group' => 'design', 'sort' => 60],
    'display_mode' => ['group' => 'display', 'sort' => 10],
    'landing_page' => ['group' => 'display', 'sort' => 20],
    'is_anchor' => ['group' => 'display', 'sort' => 30],
    'available_sort_by' => ['group' => 'display', 'sort' => 40],
    'default_sort_by' => ['group' => 'display', 'sort' => 50],
];

foreach ($attributes as $attributeCode => $attributeProp) {
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $groups[$attributeProp['group']]['id'],
        $attributeCode,
        $attributeProp['sort']
    );
}

/**
 * Install product link types
 */
$data = [
    ['link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED, 'code' => 'relation'],
    ['link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL, 'code' => 'up_sell'],
    ['link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL, 'code' => 'cross_sell'],
];

foreach ($data as $bind) {
    $installer->getConnection()
        ->insertForce($installer->getTable('catalog_product_link_type'), $bind);
}

/**
 * install product link attributes
 */
$data = [
    [
        'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
        'product_link_attribute_code' => 'position',
        'data_type' => 'int',
    ],
    [
        'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
        'product_link_attribute_code' => 'position',
        'data_type' => 'int'
    ],
    [
        'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
        'product_link_attribute_code' => 'position',
        'data_type' => 'int'
    ],
];

$installer->getConnection()
    ->insertMultiple($installer->getTable('catalog_product_link_attribute'), $data);

/**
 * Remove Catalog specified attribute options (columns) from eav/attribute table
 *
 */
$describe = $installer->getConnection()
    ->describeTable($installer->getTable('catalog_eav_attribute'));
foreach ($describe as $columnData) {
    if ($columnData['COLUMN_NAME'] == 'attribute_id') {
        continue;
    }
    $installer->getConnection()
        ->dropColumn($installer->getTable('eav_attribute'), $columnData['COLUMN_NAME']);
}

$newGeneralTabName = 'Product Details';
$newPriceTabName = 'Advanced Pricing';
$newImagesTabName = 'Image Management';
$newMetaTabName = 'Search Engine Optimization';
$autosettingsTabName = 'Autosettings';
$tabNames = [
    'General' => [
        'attribute_group_name' => $newGeneralTabName,
        'attribute_group_code' => preg_replace('/[^a-z0-9]+/', '-', strtolower($newGeneralTabName)),
        'tab_group_code' => 'basic',
        'sort_order' => 10,
    ],
    'Images' => [
        'attribute_group_name' => $newImagesTabName,
        'attribute_group_code' => preg_replace('/[^a-z0-9]+/', '-', strtolower($newImagesTabName)),
        'tab_group_code' => 'basic',
        'sort_order' => 20,
    ],
    'Meta Information' => [
        'attribute_group_name' => $newMetaTabName,
        'attribute_group_code' => preg_replace('/[^a-z0-9]+/', '-', strtolower($newMetaTabName)),
        'tab_group_code' => 'basic',
        'sort_order' => 30,
    ],
    'Prices' => [
        'attribute_group_name' => $newPriceTabName,
        'attribute_group_code' => preg_replace('/[^a-z0-9]+/', '-', strtolower($newPriceTabName)),
        'tab_group_code' => 'advanced',
        'sort_order' => 40,
    ],
    'Design' => ['attribute_group_code' => 'design', 'tab_group_code' => 'advanced', 'sort_order' => 50],
];

$entityTypeId = $installer->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
$attributeSetId = $installer->getAttributeSetId($entityTypeId, 'Default');

//Rename attribute tabs
foreach ($tabNames as $tabName => $tab) {
    $groupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $tabName);
    if ($groupId) {
        foreach ($tab as $propertyName => $propertyValue) {
            $installer->updateAttributeGroup($entityTypeId, $attributeSetId, $groupId, $propertyName, $propertyValue);
        }
    }
}

//Add new tab
$installer->addAttributeGroup($entityTypeId, $attributeSetId, $autosettingsTabName, 60);
$installer->updateAttributeGroup(
    $entityTypeId,
    $attributeSetId,
    'Autosettings',
    'attribute_group_code',
    'autosettings'
);
$installer->updateAttributeGroup($entityTypeId, $attributeSetId, 'Autosettings', 'tab_group_code', 'advanced');

//New attributes order and properties
$properties = ['is_required', 'default_value', 'frontend_input_renderer'];
$attributesOrder = [
    //Product Details tab
    'name' => [$newGeneralTabName => 10],
    'sku' => [$newGeneralTabName => 20],
    'price' => [$newGeneralTabName => 30],
    'image' => [$newGeneralTabName => 50],
    'weight' => [$newGeneralTabName => 70, 'is_required' => 0],
    'category_ids' => [$newGeneralTabName => 80],
    'description' => [$newGeneralTabName => 90, 'is_required' => 0],
    'status' => [
        $newGeneralTabName => 100,
        'is_required' => 0,
        'default_value' => 1,
        'frontend_input_renderer' => 'Magento\Framework\Data\Form\Element\Hidden',
    ],
    //Autosettings tab
    'short_description' => [$autosettingsTabName => 0, 'is_required' => 0],
    'visibility' => [$autosettingsTabName => 20, 'is_required' => 0],
    'news_from_date' => [$autosettingsTabName => 30],
    'news_to_date' => [$autosettingsTabName => 40],
    'country_of_manufacture' => [$autosettingsTabName => 50],
];

foreach ($attributesOrder as $key => $value) {
    $attribute = $installer->getAttribute($entityTypeId, $key);
    if ($attribute) {
        foreach ($value as $propertyName => $propertyValue) {
            if (in_array($propertyName, $properties)) {
                $installer->updateAttribute($entityTypeId, $attribute['attribute_id'], $propertyName, $propertyValue);
            } else {
                $installer->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $propertyName,
                    $attribute['attribute_id'],
                    $propertyValue
                );
            }
        }
    }
}

foreach (['status', 'visibility'] as $attributeCode) {
    $installer->updateAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        $attributeCode,
        'is_required_in_admin_store',
        '1'
    );
}
