<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Catalog\Model\ResourceModel\Product\Collection');
foreach ($productCollection as $product) {
    $product->delete();
}

$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config');
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');
if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
    && $attribute->getId()
) {
    $attribute->delete();
}
$eavConfig->clear();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
