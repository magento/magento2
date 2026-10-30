<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$attributeCodes = [
    'test_attribute',
];

foreach ($attributeCodes as $attributeCode) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->loadByCode('catalog_product', $attributeCode);
    if ($attribute->getId()) {
        $attribute->delete();
    }
}
