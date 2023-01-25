<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Weee/_files/product_with_fpt.php');

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$entityModel = Bootstrap::getObjectManager()->create(\Magento\Eav\Model\Entity::class);
$entityTypeId = $entityModel->setType(Product::ENTITY)->getTypeId();
$groupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
$attribute = Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$attribute->setAttributeCode(
    'fixed_product_attribute'
)->setEntityTypeId(
    $entityTypeId
)->setAttributeGroupId(
    $groupId
)->setAttributeSetId(
    $attributeSetId
)->setFrontendLabel(
    'fixed_product_attribute_front_label'
)->setFrontendInput(
    'weee'
)->setIsUserDefined(
    1
)->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);

$product = $product->loadByAttribute('sku', 'simple-with-ftp');
if ($product && $product->getId()) {
    $product->setFixedProductAttribute(
        [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.00, 'delete' => '']]
    )->save();
}
