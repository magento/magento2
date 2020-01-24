<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;

Bootstrap::getInstance()->reinitialize();

/** @var $eavConfig Config */
$eavConfig = Bootstrap::getObjectManager()->get(Config::class);

/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);

$attributesData = [
    [
        'code' => 'custom_attr_1',
        'label' => 'custom_attr_1',
    ],
    [
        'code' => 'custom_attr_2',
        'label' => 'custom_attr_2',
    ],
];

foreach ($attributesData as $attributeData) {
    $attribute = $eavConfig->getAttribute('catalog_product', $attributeData['code']);

    $eavConfig->clear();


    if (!$attribute->getId()) {

        /** @var $attribute Attribute */
        $attribute = Bootstrap::getObjectManager()->create(
            Attribute::class
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepositoryInterface::class);

        $attribute->setData(
            [
                'attribute_code' => $attributeData['code'],
                'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
                'is_global' => 1,
                'is_user_defined' => 1,
                'frontend_input' => 'select',
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
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'frontend_label' => $attributeData['label'],
                'backend_type' => 'int',
                'option' => [
                    'value' => [
                        'option_0' => [
                            $attributeData['label'] . ' Option 1'
                        ],
                        'option_1' => [
                            $attributeData['label'] . ' Option 2'
                        ],
                        'option_2' => [
                            $attributeData['label'] . ' Option 3'
                        ],
                        'option_3' => [
                            $attributeData['label'] . ' Option 4'
                        ]
                    ],
                    'order' => [
                        'option_0' => 1,
                        'option_1' => 2,
                        'option_2' => 3,
                        'option_3' => 4
                    ],
                ],
            ]
        );

        $attributeRepository->save($attribute);

        /* Assign attribute to attribute set */
        $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
    }
}

$eavConfig->clear();
