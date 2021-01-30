<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/**
 * @var StoreManagerInterface $storeManager
 */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$option = [
    'value' => [
        'car' => ['Car'],
        'ship' => ['Ship'],
    ],
    'order' => [
        'car' => 1,
        'ship' => 2,
    ],
];

$store = $storeManager->getDefaultStoreView();
$labels[0] = 'Ship_admin';
$labels[$store->getId()] = 'Ship_' . $store->getCode();
$option['value']['ship'] = $labels;

/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);

/** @var EavAttribute $attribute */
$selectAttribute = $objectManager->create(EavAttribute::class);
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
        'backend_model' => ArrayBackend::class,
        'option' => $option,
    ]
);
$selectAttribute->save();

$installer->addAttributeToGroup(
    'catalog_product',
    'Default',
    'General',
    $selectAttribute->getId()
);
