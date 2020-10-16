<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Attribute $attribute */
$attribute = $objectManager->create(Attribute::class);
$attribute->loadByCode(4, 'foo');

if ($attribute->getId()) {
    $attribute->delete();
}

/** @var Set $attributeSet */
$attributeSet = $objectManager->create(Set::class)->load('test_attribute_set', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
