<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Create multiselect attribute
 */
require __DIR__ . '/multiselect_attribute.php';

/** Create product with options and multiselect attribute */

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);

/** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
);
$options->setAttributeFilter($attribute->getId());
$optionIds = $options->getAllIds();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId($optionIds[0] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 1')
    ->setSku('simple_ms_1')
    ->setPrice(10)
    ->setDescription('Hello " &amp;" Bring the water bottle when you can!')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute([$optionIds[0]])
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId($optionIds[1] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 2')
    ->setSku('simple_ms_2')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute([$optionIds[1], $optionIds[2], $optionIds[3]])
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();
