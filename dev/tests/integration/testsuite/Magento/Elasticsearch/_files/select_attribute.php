<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = $objectManager->create(\Magento\Catalog\Setup\CategorySetup::class);

/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
$selectAttribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$selectAttribute->setData(
    [
        'attribute_code' => 'select_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'select',
        'is_unique' => 0,
        'is_required' => 0,
        'is_searchable' => 1,
        'is_visible_in_advanced_search' => 0,
        'is_comparable' => 0,
        'is_filterable' => 1,
        'is_filterable_in_search' => 0,
        'is_used_for_promo_rules' => 0,
        'is_html_allowed_on_front' => 1,
        'is_visible_on_front' => 0,
        'used_in_product_listing' => 0,
        'used_for_sort_by' => 0,
        'frontend_label' => ['Select Attribute'],
        'backend_type' => 'varchar',
        'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
        'option' => [
            'value' => [
                'chair' => ['Chair'],
                'table' => ['Table'],
            ],
            'order' => [
                'chair' => 1,
                'table' => 2,
            ],
        ],
    ]
);
$selectAttribute->save();

$installer->addAttributeToGroup(
    'catalog_product',
    'Default',
    'General',
    $selectAttribute->getId()
);
