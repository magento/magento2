<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    3331
)->setCreatedAt(
    '2017-06-23 09:50:07'
)->setName(
    'Category with slash/ symbol'
)->setParentId(
    2
)->setPath(
    '1/2/3331'
)->setLevel(
    2
)->setAvailableSortBy(
    ['position', 'name']
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();
