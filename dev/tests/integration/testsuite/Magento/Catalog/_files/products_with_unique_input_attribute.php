<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/unique_input_attribute.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$eavConfig = $objectManager->get(Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'unique_input_attribute');

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('Simple Product with unique input attribute')
    ->setSku('simple product with unique input attribute')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setData($attribute->getAttributeCode(), 'unique value')
    ->save();
