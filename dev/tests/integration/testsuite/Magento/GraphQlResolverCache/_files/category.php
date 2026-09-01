<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$storeManager = $objectManager->get(StoreManagerInterface::class);
$category = $objectManager->get(CategoryFactory::class)->create();
$category->setName('GraphQL Cache Category')
    ->setParentId((int) $storeManager->getStore()->getRootCategoryId())
    ->setIsActive(true)
    ->setIncludeInMenu(true)
    ->setAvailableSortBy(['position', 'name'])
    ->setDefaultSortBy('position');
$objectManager->get(CategoryRepositoryInterface::class)->save($category);
