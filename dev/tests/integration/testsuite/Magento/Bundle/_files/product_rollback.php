<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*
 * Since the bundle product creation GUI doesn't allow to choose values for bundled products' custom options,
 * bundled items should not contain products with required custom options.
 * However, if to create such a bundle product, it will be always out of stock.
 */
require __DIR__ . '/../../../Magento/Catalog/_files/products_rollback.php';

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(3);
if ($product->getId()) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
