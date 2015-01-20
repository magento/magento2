<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $simpleProduct \Magento\Catalog\Model\Product */
$simpleProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$simpleProduct->load(2);
if ($simpleProduct->getId()) {
    $simpleProduct->delete();
}

/** @var $virtualProduct \Magento\Catalog\Model\Product */
$virtualProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$virtualProduct->load(21);
if ($virtualProduct->getId()) {
    $virtualProduct->delete();
}

/** @var $groupedProduct \Magento\Catalog\Model\Product */
$groupedProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$groupedProduct->load(9);
if ($groupedProduct->getId()) {
    $groupedProduct->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
