<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiselect_attribute.php');

/** Create product with options and multiselect attribute */
$eavConfig = Bootstrap::getObjectManager()->get(Config::class);
$multiSelectAttribute = $eavConfig->getAttribute(Product::ENTITY, 'multiselect_attribute');
$multiSelectAttributeText = $eavConfig->getAttribute(Product::ENTITY, 'multiselect_attribute_text');

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);

/** @var $multiSelectAttributeOptions \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$multiSelectAttributeOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
);

$multiSelectAttributeOptions->setAttributeFilter($multiSelectAttribute->getId());
$multiSelectAttributeOptionsIds = $multiSelectAttributeOptions->getAllIds();


/** @var $multiSelectAttributeOptionsText \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$multiSelectAttributeOptionsText = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
);

$multiSelectAttributeOptionsText->setAttributeFilter($multiSelectAttributeText->getId());
$multiSelectAttributeOptionsTextIds = $multiSelectAttributeOptionsText->getAllIds();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId($multiSelectAttributeOptionsIds[0] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 1')
    ->setSku('simple_ms_1')
    ->setPrice(10)
    ->setDescription('Hello " &amp;" Bring the water bottle when you can!')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute([$multiSelectAttributeOptionsIds[0]])
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId($multiSelectAttributeOptionsIds[1] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 2')
    ->setSku('simple_ms_2')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute(
        [$multiSelectAttributeOptionsIds[1], $multiSelectAttributeOptionsIds[2], $multiSelectAttributeOptionsIds[3]]
    )
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId($multiSelectAttributeOptionsTextIds[2] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect Text')
    ->setSku('simple_ms_3')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttributeText(array_values($multiSelectAttributeOptionsTextIds))
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();
