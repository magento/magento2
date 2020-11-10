<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);

/** @var $category Category */
$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setId(3)
    ->setName('Category 123')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIncludeInMenu(false)
    ->setIsActive(true)
    ->setPosition(1)
    ->save();
