<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    $repository->delete($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Entity already deleted
} catch (\Magento\Framework\Exception\StateException $e) {

}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
