<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\TestFramework\Helper\Bootstrap;

$installer = Bootstrap::getObjectManager()->create(
    CategorySetup::class,
    ['resourceName' => 'catalog_setup']
);

/* Create multiselect attribute. */
$multiselectAttribute = Bootstrap::getObjectManager()->create(
    Attribute::class
);
$multiselectAttribute->setData(
    [
        'attribute_code' => 'multiselect_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'frontend_input' => 'multiselect',
        'is_searchable' => 1,
        'option' => [
            'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
            'order' => ['option_0' => 1, 'option_1' => 2],
        ],
        'backend_type' => 'varchar',
    ]
);
$multiselectAttribute->save();

/* Assign attribute to attribute set. */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $multiselectAttribute->getId());

/** Create simple product with multiselect attribute. */
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    $installer->getAttributeSetId('catalog_product', 'Default')
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product With Multiselect Atrribute '
)->setSku(
    'simple_product_with_multiselect_attribute'
)->setPrice(
    99
)->setCategoryIds(
    [2]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    ['use_config_manage_stock' => 1, 'qty' => 5, 'is_in_stock' => 1]
)->save();

Bootstrap::getObjectManager()->get(Action::class)->updateAttributes(
    [$product->getId()],
    [
        $multiselectAttribute->getAttributeCode() => $multiselectAttribute->getOptions()[2]->getValue(),
    ],
    $product->getStoreId()
);
