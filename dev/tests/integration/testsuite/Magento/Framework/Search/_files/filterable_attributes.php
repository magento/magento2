<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\TestFramework\Helper\Bootstrap;

/* Create attribute */
/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(
    CategorySetup::class,
    ['resourceName' => 'catalog_setup']
);
$productEntityTypeId = $installer->getEntityTypeId(
    \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
);

$selectOptions = [];
$selectAttributes = [];
foreach (range(1, 2) as $index) {
    /** @var $selectAttribute Attribute */
    $selectAttribute = Bootstrap::getObjectManager()->create(
        Attribute::class
    );
    $selectAttribute->setData(
        [
            'attribute_code' => 'select_attribute_' . $index,
            'entity_type_id' => $productEntityTypeId,
            'is_global' => 1,
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
            'frontend_label' => ['Select Attribute'],
            'backend_type' => 'int',
            'option' => [
                'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
                'order' => ['option_0' => 1, 'option_1' => 2],
            ],
        ]
    );
    $selectAttribute->save();
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup($productEntityTypeId, 'Default', 'General', $selectAttribute->getId());

    /** @var $selectOptions Collection */
    $selectOption = Bootstrap::getObjectManager()->create(
        Collection::class
    );
    $selectOption->setAttributeFilter($selectAttribute->getId());
    $selectAttributes[$index] = $selectAttribute;
    $selectOptions[$index] = $selectOption;
}

$dateAttribute = Bootstrap::getObjectManager()->create(Attribute::class);
$dateAttribute->setData(
    [
        'attribute_code' => 'date_attribute',
        'entity_type_id' => $productEntityTypeId,
        'is_global' => 1,
        'is_filterable' => 1,
        'backend_type' => 'datetime',
        'frontend_input' => 'date',
        'frontend_label' => 'Test Date',
        'is_searchable' => 1,
        'is_filterable_in_search' => 1,
    ]
);
$dateAttribute->save();
/* Assign attribute to attribute set */
$installer->addAttributeToGroup($productEntityTypeId, 'Default', 'General', $dateAttribute->getId());

$productAttributeSetId = $installer->getAttributeSetId($productEntityTypeId, 'Default');
/* Create simple products per each first attribute option */
foreach ($selectOptions[1] as $option) {
    /** @var $product Product */
    $product = Bootstrap::getObjectManager()->create(
        Product::class
    );
    $product->setTypeId(
        Type::TYPE_SIMPLE
    )->setAttributeSetId(
        $productAttributeSetId
    )->setWebsiteIds(
        [1]
    )->setName(
        'Simple Product ' . $option->getId()
    )->setSku(
        'simple_product_' . $option->getId()
    )->setPrice(
        99
    )->setCategoryIds(
        [2]
    )->setVisibility(
        Visibility::VISIBILITY_BOTH
    )->setStatus(
        Status::STATUS_ENABLED
    )->setStockData(
        ['use_config_manage_stock' => 1, 'qty' => 5, 'is_in_stock' => 1]
    )->save();

    Bootstrap::getObjectManager()->get(
        Action::class
    )->updateAttributes(
        [$product->getId()],
        [
            $selectAttributes[1]->getAttributeCode() => $option->getId(),
            $selectAttributes[2]->getAttributeCode() => $selectOptions[2]->getLastItem()->getId(),
            $dateAttribute->getAttributeCode() => '10/30/2000',
        ],
        $product->getStoreId()
    );
}
