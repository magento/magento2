<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);
/** @var $attributeFactory AttributeFactory */
$attributeFactory = $objectManager->get(AttributeFactory::class);
/** @var $attribute Attribute */
$attribute = $attributeFactory->create();
$entityTypeId = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
if (!$attribute->loadByCode($entityTypeId, 'multiselect_attribute_with_html')->getAttributeId()) {
    $attribute->setData(
        [
            'attribute_code' => 'multiselect_attribute_with_html',
            'entity_type_id' => $entityTypeId,
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
            'backend_type' => 'varchar',
            'backend_model' => ArrayBackend::class,
            'option' => [
                'value' => [
                    'option_1' => ['<h2>Option 1</h2>'],
                    'option_2' => ['<h2>Option 2</h2>'],
                    'option_3' => ['<h2>Option 3</h2>'],
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                    'option_3' => 3,
                ],
            ],
        ]
    );
    $attribute->save();
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(
        $entityTypeId,
        'Default',
        'General',
        $attribute->getId()
    );
}
