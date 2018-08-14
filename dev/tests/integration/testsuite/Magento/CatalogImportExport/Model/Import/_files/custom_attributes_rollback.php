<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
