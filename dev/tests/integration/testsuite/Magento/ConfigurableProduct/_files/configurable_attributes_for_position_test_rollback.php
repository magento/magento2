<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$productCollection = Bootstrap::getObjectManager()
    ->get(Collection::class);
foreach ($productCollection as $product) {
    $product->delete();
}

$attributesToDelete = [
    'custom_attr_1',
    'custom_attr_2',
];

foreach ($attributesToDelete as $attributeToDelete) {
    $eavConfig = Bootstrap::getObjectManager()->get(Config::class);
    $attribute = $eavConfig->getAttribute('catalog_product', $attributeToDelete);
    if ($attribute instanceof AbstractAttribute
        && $attribute->getId()
    ) {
        $attribute->delete();
    }
    $eavConfig->clear();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
