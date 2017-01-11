<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$attributesCode = ['test_advanced_search', 'test_quick_search', 'test_catalog_view'];

foreach (['test_quick_search', 'test_catalog_view'] as $code) {
    $attribute = $eavConfig->getAttribute('catalog_product', $code);
    if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
        && $attribute->getId()
    ) {
        $attribute->delete();
    }
}
$eavConfig->clear();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
