<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require 'fixed_discount_rollback.php';

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

<<<<<<< HEAD
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var SearchCriteriaBuilder $productSearchCriteriaBuilder */
$productSearchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
$searchCriteria = $productSearchCriteriaBuilder->addFilter('sku', ['simple1', 'simple2', 'simple3'], 'in')
    ->create();
<<<<<<< HEAD
$productList = $productRepository->getList($searchCriteria)
    ->getItems();
=======
$productList = $productRepository->getList($searchCriteria)->getItems();

$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
if (!empty($productList)) {
    foreach ($productList as $product) {
        $productRepository->delete($product);
    }
}

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
<<<<<<< HEAD
$searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', '100000015')
    ->create();
$items = $quoteRepository->getList($searchCriteria)
    ->getItems();
=======
$searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', '100000015')->create();
$items = $quoteRepository->getList($searchCriteria)->getItems();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

if (!empty($items)) {
    $quote = array_pop($items);
    $quoteRepository->delete($quote);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
