<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$attributeCodes = [
    'test_attribute',
<<<<<<< HEAD
    ];
=======
];
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

foreach ($attributeCodes as $attributeCode) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->loadByCode('catalog_product', $attributeCode);
    if ($attribute->getId()) {
        $attribute->delete();
    }
}
