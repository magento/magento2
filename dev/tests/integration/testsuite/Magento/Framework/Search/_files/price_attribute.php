<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = $objectManager->create(
    \Magento\Catalog\Setup\CategorySetup::class,
    ['resourceName' => 'catalog_setup']
);

/** @var $selectAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$priceAttribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$priceAttribute->setData(
    [
        'attribute_code' => 'price_attribute',
        'entity_type_id' => $installer->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY),
        'is_global' => 1,
        'is_searchable' => 1,
        'is_filterable' => 1,
        'backend_type' => 'decimal',
        'frontend_input' => 'price',
        'frontend_label' => 'Test Price',
    ]
);
$priceAttribute->save();

$setId = $installer->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);
$groupId = $installer->getDefaultAttributeGroupId(\Magento\Catalog\Model\Product::ENTITY, $setId);

/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', $setId, $groupId, $priceAttribute->getId());

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($setId)
    ->setWebsiteIds([1])
    ->setName('Simple Product with custom price attribute')
    ->setSku('simple_product_with_custom_price_attribute')
    ->setPrice(1)
    ->setCategoryIds([2])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 5, 'is_in_stock' => 1])
    ->save();

$objectManager->get(\Magento\Catalog\Model\Product\Action::class)
    ->updateAttributes(
        [$product->getId()],
        [$priceAttribute->getAttributeCode() => '19.89'],
        \Magento\Store\Model\Store::DEFAULT_STORE_ID
    );
