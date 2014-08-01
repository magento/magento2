<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
$installer->installEntities();
// Create Root Catalog Node
$installer->createCategory()->load(
    1
)->setId(
    1
)->setStoreId(
    0
)->setPath(
    1
)->setLevel(
    0
)->setPosition(
    0
)->setChildrenCount(
    0
)->setName(
    'Root Catalog'
)->setInitialSetupFlag(
    true
)->save();

$category = $installer->createCategory();

$installer->createCategory()->setStoreId(
    0
)->setName(
    'Default Category'
)->setDisplayMode(
    'PRODUCTS'
)->setAttributeSetId(
    $category->getDefaultAttributeSetId()
)->setIsActive(
    1
)->setPath(
    '1'
)->setInitialSetupFlag(
    true
)->save();

$data = array(
    'scope' => 'default',
    'scope_id' => 0,
    'path' => \Magento\Catalog\Helper\Category::XML_PATH_CATEGORY_ROOT_ID,
    'value' => $category->getId()
);
$installer->getConnection()->insertOnDuplicate($installer->getTable('core_config_data'), $data, array('value'));

$installer->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', 'Design', 6);

$entityTypeId = $installer->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// update General Group
//$installer->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'attribute_group_name', 'General Information');
$installer->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'sort_order', '10');

$groups = array(
    'display' => array('name' => 'Display Settings', 'sort' => 20, 'id' => null),
    'design' => array('name' => 'Custom Design', 'sort' => 30, 'id' => null)
);

foreach ($groups as $k => $groupProp) {
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupProp['name'], $groupProp['sort']);
    $groups[$k]['id'] = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupProp['name']);
}

// update attributes group and sort
$attributes = array(
    'custom_design' => array('group' => 'design', 'sort' => 10),
    // 'custom_design_apply' => array('group' => 'design', 'sort' => 20),
    'custom_design_from' => array('group' => 'design', 'sort' => 30),
    'custom_design_to' => array('group' => 'design', 'sort' => 40),
    'page_layout' => array('group' => 'design', 'sort' => 50),
    'custom_layout_update' => array('group' => 'design', 'sort' => 60),
    'display_mode' => array('group' => 'display', 'sort' => 10),
    'landing_page' => array('group' => 'display', 'sort' => 20),
    'is_anchor' => array('group' => 'display', 'sort' => 30),
    'available_sort_by' => array('group' => 'display', 'sort' => 40),
    'default_sort_by' => array('group' => 'display', 'sort' => 50)
);

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
$data = array(
    array('link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED, 'code' => 'relation'),
    array('link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL, 'code' => 'up_sell'),
    array('link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL, 'code' => 'cross_sell')
);

foreach ($data as $bind) {
    $installer->getConnection()->insertForce($installer->getTable('catalog_product_link_type'), $bind);
}

/**
 * install product link attributes
 */
$data = array(
    array(
        'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
        'product_link_attribute_code' => 'position',
        'data_type' => 'int'
    ),
    array(
        'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
        'product_link_attribute_code' => 'position',
        'data_type' => 'int'
    ),
    array(
        'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
        'product_link_attribute_code' => 'position',
        'data_type' => 'int'
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('catalog_product_link_attribute'), $data);

/**
 * Remove Catalog specified attribute options (columns) from eav/attribute table
 *
 */
$describe = $installer->getConnection()->describeTable($installer->getTable('catalog_eav_attribute'));
foreach ($describe as $columnData) {
    if ($columnData['COLUMN_NAME'] == 'attribute_id') {
        continue;
    }
    $installer->getConnection()->dropColumn($installer->getTable('eav_attribute'), $columnData['COLUMN_NAME']);
}
