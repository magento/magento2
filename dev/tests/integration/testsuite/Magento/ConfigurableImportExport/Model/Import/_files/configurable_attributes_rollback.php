<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
/** @var $attributes array */
$attributes = [
    [
        'code' => 'test_attribute_1',
        'label' => 'Test attribute 1'
    ],
    [
        'code' => 'test_attribute_2',
        'label' => 'Test attribute 2'
    ]
];

foreach ($attributes as $attribute) {
    $attribute = $eavConfig->getAttribute('catalog_product', $attribute['code']);
    if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
        && $attribute->getId()
    ) {
        $attribute->delete();
    }
}
$eavConfig->clear();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
