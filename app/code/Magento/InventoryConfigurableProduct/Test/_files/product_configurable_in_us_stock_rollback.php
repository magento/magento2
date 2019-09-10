<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);




$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$searchCriteria = $searchCriteriaBuilder->addFilter('sku', ['simple_10', 'simple_20'], 'in')
    ->create();
$list = $productRepository->getList($searchCriteria);

foreach ($list->getItems() as $product) {
    $productRepository->delete($product);
}

try {
    $product = $productRepository->get('configurable');
} catch (NoSuchEntityException $e) {
    //Product already removed
}

$productRepository->delete($product);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
