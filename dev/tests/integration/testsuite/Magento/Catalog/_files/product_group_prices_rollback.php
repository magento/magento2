<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ProductRepository::class
);
try {
    $product = $repository->get('simple', false, null, true);
    $product->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Entity already deleted
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
