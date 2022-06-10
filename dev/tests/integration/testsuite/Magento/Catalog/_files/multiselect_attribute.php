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
/** @var $attributeMultiselect \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attributeMultiselect = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);

/** @var $attributeMultiselectText \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attributeMultiselectText = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);

$valueOptionArray = [];
$orderArray = [];

for ($i = 1; $i < 200; $i++) {
    $valueOptionArray[sprintf('option_%d', $i)] = [sprintf('Multiselect option %d', $i)];
    $orderArray[sprintf('option_%d', $i)] = $i;
}

$entityType = $installer->getEntityTypeId('catalog_product');
if (!$attributeMultiselect->loadByCode($entityType, 'multiselect_attribute')->getAttributeId()) {
    $attributeMultiselect->setData(
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
                'value' => [
                    'option_1' => ['Option 1'],
                    'option_2' => ['Option 2'],
                    'option_3' => ['Option 3'],
                    'option_4' => ['Option 4 "!@#$%^&*']
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                    'option_3' => 3,
                    'option_4' => 4,
                ],
            ],
        ]
    );
    $attributeMultiselect->save();

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attributeMultiselect->getId());
}


if (!$attributeMultiselectText->loadByCode($entityType, 'multiselect_attribute_text')->getAttributeId()) {
    $attributeMultiselectText->setData(
        [
            'attribute_code' => 'multiselect_attribute_text',
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
    $attributeMultiselectText->save();

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attributeMultiselectText->getId());
}
