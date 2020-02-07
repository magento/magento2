<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\Data\CategoryInterfaceFactory $categoryFactory */
$categoryFactory = $objectManager->get(\Magento\Catalog\Api\Data\CategoryInterfaceFactory::class);

/** @var \Magento\Catalog\Api\Data\CategoryInterface $category */
$category = $categoryFactory->create(
    [
        'data' => [
            'name' => 'Category With Wrong Path',
            'parent_id' => 2,
            'path' => 'wrong/path',
            'level' => 2,
            'available_sort_by' =>['position', 'name'],
            'default_sort_by' => 'name',
            'is_active' => true,
            'position' => 1,
        ],
    ]
);

$category->isObjectNew(true);
$category->save();
