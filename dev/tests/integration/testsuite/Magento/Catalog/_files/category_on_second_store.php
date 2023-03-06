<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/store.php');

$objectManager = Bootstrap::getObjectManager();
$storeManager = $objectManager->get(StoreManagerInterface::class);
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
$executeInStoreContext = $objectManager->get(ExecuteInStoreContext::class);

$currentStore = $storeManager->getStore();
$secondStore = $storeManager->getStore('test');
$category = $categoryRepository->get(333);
$category->setName('Category 1 Second');
$category->setUrlKey('category-1-second-url-key');
$executeInStoreContext->execute($secondStore, function ($categoryRepository, $category) {
    $categoryRepository->save($category);
}, $categoryRepository, $category);
