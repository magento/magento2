<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var $objectManager ObjectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$quote = $objectManager->create(Quote::class);
$quote->load('quoteWithVirtualProduct', 'reserved_order_id')->delete();

/**
 * @var ProductRepositoryInterface $productRepository
 */
$productRepository = Bootstrap::getObjectManager()
    ->get(ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('tableRate-1', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //Product already removed
}

try {
    $customDesignProduct = $productRepository->get('tableRate-2', false, null, true);
    $productRepository->delete($customDesignProduct);
} catch (NoSuchEntityException $e) {
    //Product already removed
}

/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = Bootstrap::getObjectManager()
    ->get(StockRegistryStorage::class);
$stockRegistryStorage->clean();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
