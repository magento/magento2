<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
$productCollection = Bootstrap::getObjectManager()
    ->create(Product::class)
    ->getCollection();
foreach ($productCollection as $product) {
    $product->delete();
}

/** @var $attribute Attribute */
$attribute = Bootstrap::getObjectManager()->create(
    Attribute::class
);
/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);
$productEntityTypeId = $installer->getEntityTypeId(
    \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
);
foreach (range(1, 2) as $index) {
    $attribute->loadByCode($productEntityTypeId, 'select_attribute_' . $index);
    if ($attribute->getId()) {
        $attribute->delete();
    }
}
$attribute->loadByCode($productEntityTypeId, 'date_attribute');
if ($attribute->getId()) {
    $attribute->delete();
}

$attribute->loadByCode($productEntityTypeId, 'decimal_attribute');
if ($attribute->getId()) {
    $attribute->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
