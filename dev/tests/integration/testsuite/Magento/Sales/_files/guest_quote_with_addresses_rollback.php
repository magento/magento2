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
/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);

$quote->load('guest_quote', 'reserved_order_id');

$quoteId = $quote->getId();
if (null !== $quoteId) {
    $quote->delete();
    $quoteIdMask->delete($quoteId);
}

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('simple-product-guest-quote', false, null, true);
    $productRepository->delete($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

/** @var \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);
$stockRegistryStorage->clean();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
