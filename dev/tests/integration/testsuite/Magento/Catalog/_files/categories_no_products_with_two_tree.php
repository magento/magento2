<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories_no_products.php');

$objectManager = Bootstrap::getObjectManager();
$categoryFactory = $objectManager->get(CategoryFactory::class);
$categoryResource = $objectManager->create(CategoryResource::class);
$categoryCollectionFactory = $objectManager->get(CollectionFactory::class);
/** @var Collection $categoryCollection */
$categoryCollection = $categoryCollectionFactory->create();

/** @var $category2 Category */
$category2 = $categoryCollection
    ->addAttributeToFilter(CategoryInterface::KEY_NAME, 'Category 2')
    ->setPageSize(1)
    ->getFirstItem();

/** @var $category21 Category */
$category21 = $categoryFactory->create();
$category21->isObjectNew(true);
$category21->setName('Category 2.1')
    ->setParentId($category2->getId())
    ->setPath($category2->getPath())
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$categoryResource->save($category21);

/** @var $category22 Category */
$category22 = $categoryFactory->create();
$category22->isObjectNew(true);
$category22->setName('Category 2.2')
    ->setParentId($category2->getId())
    ->setPath($category2->getPath())
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2);
$categoryResource->save($category22);

/** @var $category221 Category */
$category221 = $categoryFactory->create();
$category221->isObjectNew(true);
$category221->setName('Category 2.2.1')
    ->setParentId($category22->getId())
    ->setPath($category22->getPath())
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$categoryResource->save($category221);
