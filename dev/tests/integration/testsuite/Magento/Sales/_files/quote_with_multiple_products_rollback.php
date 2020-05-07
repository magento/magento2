<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $quote Quote */
$quote = $objectManager->create(Quote::class);
$quote->load('tableRate', 'reserved_order_id');
if ($quote->getId()) {
    $quote->delete();
}

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager
    ->create(ProductRepositoryInterface::class);

try {
    $firstProduct = $productRepository->get('simple-tableRate-1', false, null, true);
    $productRepository->delete($firstProduct);
} catch (NoSuchEntityException $exception) {
    //Product already removed
}
try {
    $secondProduct = $productRepository->get('simple-tableRate-2', false, null, true);
    $productRepository->delete($secondProduct);
} catch (NoSuchEntityException $exception) {
    //Product already removed
}
try {
    $thirdProduct = $productRepository->get('simple-tableRate-3', false, null, true);
    $productRepository->delete($thirdProduct);
} catch (NoSuchEntityException $exception) {
    //Product already removed
}
/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = $objectManager
    ->get(StockRegistryStorage::class);
$stockRegistryStorage->removeStockItem(123);
$stockRegistryStorage->removeStockItem(124);
$stockRegistryStorage->removeStockItem(658);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
