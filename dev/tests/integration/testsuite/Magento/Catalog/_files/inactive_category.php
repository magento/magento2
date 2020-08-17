<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\TestFramework\Helper\Bootstrap as Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CategoryResource $categoryResource */
$categoryResource = $objectManager->create(CategoryResource::class);
/** @var Category $category */
$category = $objectManager->get(CategoryFactory::class)->create();
$category->isObjectNew(true);
$data = [
    'entity_id' => 111,
    'path' => '1/2/111',
    'name' => 'Test Category',
    'attribute_set_id' => $category->getDefaultAttributeSetId(),
    'parent_id' => 2,
    'is_active' => false,
    'include_in_menu' => true,
];
$category->setData($data);
$categoryResource->save($category);
