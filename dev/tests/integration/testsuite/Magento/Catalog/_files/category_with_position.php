<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    '444'
)->setName(
    'Category 1'
)->setAttributeSetId(
    '3'
)->setParentId(
    2
)->setPath(
    '1/2'
)->setLevel(
    '2'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    '5'
)->save();
