<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);

$storeCodes = ['english', 'ukrainian'];
foreach ($storeCodes as $storeCode) {
    /** @var StoreInterface $store */
    $store = $storeRepository->get($storeCode);
    if ($store->getId()) {
        $store->delete();
    }
}

/** @var CategoryRepository $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepository::class);

$categoryIds = [33, 44];
foreach ($categoryIds as $categoryId) {
    /** @var CategoryInterface $category */
    $category = $categoryRepository->get($categoryId);
    if ($category->getId()) {
        $categoryRepository->delete($category);
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
