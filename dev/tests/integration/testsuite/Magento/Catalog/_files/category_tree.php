<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
/** @var $category \Magento\Catalog\Model\Category */
$categories = [
    [
        'id' => 400,
        'name' => 'Category 1',
        'parent_id' => 2,
        'path' => '1/2/400',
        'level' => 2,
        'available_sort_by' => 'name',
        'default_sort_by' => 'name',
        'is_active' => true,
        'position' => 1,
    ],
    [
        'id' => 401,
        'name' => 'Category 1.1',
        'parent_id' => 400,
        'path' => '1/2/400/401',
        'level' => 3,
        'available_sort_by' => 'name',
        'default_sort_by' => 'name',
        'is_active' => true,
        'position' => 1
    ],
    [
        'id' => 402,
        'name' => 'Category 1.1.1',
        'parent_id' => 401,
        'path' => '1/2/400/401/402',
        'level' => 4,
        'available_sort_by' => 'name',
        'default_sort_by' => 'name',
        'is_active' => true,
        'position' => 1
    ],
];
foreach ($categories as $data) {
    $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
    $model->isObjectNew(true);
    $model->setId($data['id'])
        ->setName($data['name'])
        ->setParentId($data['parent_id'])
        ->setPath($data['path'])
        ->setLevel($data['level'])
        ->setAvailableSortBy($data['available_sort_by'])
        ->setDefaultSortBy($data['default_sort_by'])
        ->setIsActive($data['is_active'])
        ->setPosition($data['position'])
        ->save();
}
