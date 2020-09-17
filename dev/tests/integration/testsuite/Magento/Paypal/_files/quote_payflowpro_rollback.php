<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Paypal/_files/fixed_discount_rollback.php');

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var SearchCriteriaBuilder $productSearchCriteriaBuilder */
$productSearchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
$searchCriteria = $productSearchCriteriaBuilder->addFilter('sku', ['simple1', 'simple2', 'simple3'], 'in')
    ->create();
$productList = $productRepository->getList($searchCriteria)->getItems();

$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

if (!empty($productList)) {
    foreach ($productList as $product) {
        $productRepository->delete($product);
    }
}

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', '100000015')->create();
$items = $quoteRepository->getList($searchCriteria)->getItems();

if (!empty($items)) {
    $quote = array_pop($items);
    $quoteRepository->delete($quote);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
