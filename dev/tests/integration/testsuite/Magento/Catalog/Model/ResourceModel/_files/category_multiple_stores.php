<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

/** @var \Magento\Catalog\Model\CategoryFactory $factory */
$factory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\CategoryFactory::class
);
/** @var \Magento\Catalog\Model\CategoryRepository $repository */
$repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\CategoryRepository::class
);
/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Store\Model\StoreManagerInterface::class
);
/** @var \Magento\Store\Model\Store $store */
$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if (!$store->load('second_category_store', 'code')->getId()) {
    $websiteId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()->getId();
    $groupId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()->getDefaultGroupId();

    $store->setCode(
        'second_category_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Fixture Store'
    )->setSortOrder(
        10
    )->setIsActive(
        1
    );
    $store->save();
}

/** @var \Magento\Catalog\Model\Category $newCategory */
$newCategory = $factory->create();
$newCategory
    ->setName('Category')
    ->setParentId(2)
    ->setLevel(2)
    ->setPath('1/2/3')
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$repository->save($newCategory);
$currentStoreId = $storeManager->getStore()->getId();
$storeManager->setCurrentStore($storeManager->getStore($store->getId()));
$newCategory->setUrlKey('category-3-on-2');
$repository->save($newCategory);
$storeManager->setCurrentStore($storeManager->getStore($currentStoreId));
