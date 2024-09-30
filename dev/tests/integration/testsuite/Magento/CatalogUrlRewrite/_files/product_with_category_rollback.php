<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);


/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('p002', false, null, true);
$productRepository->delete($product);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('name', 'category 1')
    ->create();

/** @var CategoryListInterface $categoryList */
$categoryList = $objectManager->get(CategoryListInterface::class);
$categories = $categoryList->getList($searchCriteria)
    ->getItems();

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

foreach ($categories as $category) {
    $categoryRepository->delete($category);
}

/** @var UrlRewrite $urlRewrite */
$urlRewrite = $objectManager->create(UrlRewrite::class);
$urlRewrite->load('non-exist-product.html', 'request_path');
$urlRewrite->delete();
$urlRewrite->load('.html', 'request_path');
$urlRewrite->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/store_rollback.php');
