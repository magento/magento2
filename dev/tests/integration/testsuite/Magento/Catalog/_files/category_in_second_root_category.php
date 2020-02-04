<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Category;

require __DIR__ . '/../../Store/_files/store_with_second_root_category.php';

/** @var Category $category */
$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setName('Root2 Category 1')
    ->setParentId($rootCategory->getId())
    ->setAvailableSortBy(['position', 'name'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$categoryRepository->save($category);
