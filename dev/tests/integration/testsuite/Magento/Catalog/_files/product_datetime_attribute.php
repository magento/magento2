<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);
$entityType = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
/** @var Attribute $attribute */
$attribute = $objectManager->get(AttributeFactory::class)->create();
if (!$attribute->loadByCode($entityType, 'datetime_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'attribute_code' => 'datetime_attribute',
            'entity_type_id' => $entityType,
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'datetime',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 1,
            'used_for_sort_by' => 0,
            'frontend_label' => ['Date Time Attribute'],
            'backend_type' => 'datetime',
        ]
    );
    $attributeRepository->save($attribute);
    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}
