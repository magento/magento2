<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
/** @var $category \Magento\Catalog\Model\Category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(3)
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$urlKeys = ['url-key', 'url-key-1', 'url-key-2', 'url-key-5', 'url-key-1000', 'url-key-999', 'url-key-asdf'];

foreach ($urlKeys as $i => $urlKey) {
    $id = $i + 1;
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setStoreId(1)
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Simple Product ' . $id)
        ->setSku('simple-' . $id)
        ->setPrice(10)
        ->setCategoryIds([3])
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setUrlKey($urlKey)->setUrlPath($urlKey)
        ->save();
}
