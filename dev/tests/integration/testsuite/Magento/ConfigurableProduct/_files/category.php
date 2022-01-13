<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    333
)->setCreatedAt(
    '2021-01-14 09:50:07'
)->setName(
    'Category 1'
)->setParentId(
    2
)->setPath(
    '1/2/333'
)->setLevel(
    2
)->setAvailableSortBy(
    ['position', 'name', 'price']
)->setDefaultSortBy(
    'name'
)->addData(
    ['available_sort_by' => 'position,name,price']
)->setIsActive(
    true
)->setPosition(
    1
)->save();
