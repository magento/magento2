<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Framework\Registry $registry */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('guest_quote', 'reserved_order_id');
if ($quote->getId()) {
    $quote->delete();
}

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('simple-product-guest-quote', false, null, true);
    $productRepository->delete($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
