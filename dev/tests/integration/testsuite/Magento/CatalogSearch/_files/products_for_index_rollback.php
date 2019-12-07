<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->getInstance()->reinitialize();

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

$productSkus = [
    'index_enabled',
    'index_disabled',
    'index_visible_search',
    'index_visible_category',
    'index_visible_both',
    'index_not_visible'
];
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, $productSkus, 'in');
$result = $productRepository->getList($searchCriteriaBuilder->create());
foreach ($result->getItems() as $product) {
    $productRepository->delete($product);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
