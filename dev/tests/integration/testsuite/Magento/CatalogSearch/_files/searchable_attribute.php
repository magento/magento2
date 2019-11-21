<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Setup\EavSetup;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Attribute $attribute */
$attribute = $objectManager->create(Attribute::class);
/** @var Type $entityType */
$entityType = $objectManager->create(Type::class);
$entityType = $entityType->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);

if (!$attribute->loadByCode($entityType->getEntityTypeId(), 'test_searchable_attribute')->getId()) {
    /** @var EavSetup $installer */
    $installer = $objectManager->create(EavSetup::class);
    $attribute->setData(
        [
            'attribute_code' => 'test_searchable_attribute',
            'entity_type_id' => $installer->getEntityTypeId(Product::ENTITY),
            'is_global' => 0,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 1,
            'is_visible_in_advanced_search' => 1,
            'is_comparable' => 0,
            'is_filterable' => 1,
            'is_filterable_in_search' => 1,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'frontend_label' => ['Test Drop-Down Attribute'],
            'backend_type' => 'int',
            'option' => [
                'value' => [
                    'option_1' => ['Option 1'],
                    'option_2' => ['Option 2'],
                    'option_3' => ['Option 3'],
                    'option_4' => ['Simple']
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
    $attribute->save();

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(Product::ENTITY, 'Default', 'Attributes', $attribute->getId());
}
