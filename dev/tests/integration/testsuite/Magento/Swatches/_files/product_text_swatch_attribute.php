<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$installer = $objectManager->create(CategorySetup::class);
$attribute = $objectManager->create(AttributeFactory::class)->create();
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$entityType = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
if (!$attribute->loadByCode($entityType, 'text_swatch_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'frontend_label' => ['Text swatch attribute'],
            'entity_type_id' => $entityType,
            'frontend_input' => 'select',
            'backend_type' => 'int',
            'is_required' => '0',
            'attribute_code' => 'text_swatch_attribute',
            'is_global' => '1',
            'is_user_defined' => 1,
            'is_unique' => '0',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '1',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '0',
            'swatch_input_type' => 'text',
            'optiontext' => [
                'order' => [
                    'option_0' => 1,
                    'option_1' => 2,
                    'option_3' => 3,
                ],
                'value' => [
                    'option_0' => ['Option 1'],
                    'option_1' => ['Option 2'],
                    'option_2' => ['Option 3'],
                ],
            ],
            'swatchtext' => [
                'value' => [
                    'option_0' => ['Swatch 1'],
                    'option_1' => ['Swatch 2'],
                    'option_2' => ['Swatch 3'],
                ],
            ],
        ]
    );
    $attribute->save();
    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}
