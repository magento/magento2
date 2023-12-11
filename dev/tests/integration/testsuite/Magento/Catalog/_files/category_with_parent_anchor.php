<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var CategoryFactory $categoryFactory */
$categoryFactory = $objectManager->get(CategoryFactory::class);
/** @var CategoryRepository $categoryRepository */
$categoryRepository = $objectManager->create(CategoryRepository::class);
$parentCategory = $categoryFactory->create();
$attributeSetId = $parentCategory->getDefaultAttributeSetId();
$parentCategory->isObjectNew(true);
$parentCategoryData = [
    'name' => 'Parent category',
    'attribute_set_id' => $attributeSetId,
    'parent_id' => 2,
    'is_active' => true,
    'is_anchor' => true,
];
$parentCategory->setData($parentCategoryData);
$parentCategory = $categoryRepository->save($parentCategory);

$category = $categoryFactory->create();
$category->isObjectNew(true);
$categoryData = [
    'name' => 'Child category',
    'attribute_set_id' => $attributeSetId,
    'parent_id' => $parentCategory->getId(),
    'is_active' => true,
];
$category->setData($categoryData);
$categoryRepository->save($category);

//save parent category to set "children" attribute
$categoryRepository->save($parentCategory);
