<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* Create attribute */
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);

$valueOptionArray = [];
$orderArray = [];

for ($i = 1; $i < 200; $i++) {
    $valueOptionArray[sprintf('option_%d', $i)] = [sprintf('Multiselect option %d', $i)];
    $orderArray[sprintf('option_%d', $i)] = $i;
}

$entityType = $installer->getEntityTypeId('catalog_product');
if (!$attribute->loadByCode($entityType, 'multiselect_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'attribute_code' => 'multiselect_attribute',
            'entity_type_id' => $entityType,
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'multiselect',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 1,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'frontend_label' => ['Multiselect Attribute'],
            'backend_type' => 'text',
            'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
            'option' => [
                'value' => $valueOptionArray,
                'order' => $orderArray
            ],
        ]
    );
    $attribute->save();

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}
